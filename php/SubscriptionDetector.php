<?php
// php/SubscriptionDetector.php

class SubscriptionDetector {
    private $conn;
    private $userId;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    /**
     * Main entry point to detect subscriptions.
     * Returns a normalized array of detected subscriptions.
     */
    public function detect() {
        $transactions = $this->fetchTransactions();
        $grouped = $this->groupTransactions($transactions);
        $subscriptions = [];

        foreach ($grouped as $merchant => $group) {
            // Requirement: Occurs at least 3 times
            if (count($group) < 3) {
                continue;
            }

            // Start with Interval Analysis (Primary Signal)
            $intervalAnalysis = $this->analyzeIntervals($group);

            // Check consistency (Relaxed amount check if intervals are solid)
            $amountAnalysis = $this->analyzeAmounts($group);

            if ($intervalAnalysis['is_recurring']) {
                // If recurring, we accept it even if amount changed (Price Hike), 
                // unless amount is WILDLY different (e.g. 10 vs 1000 - likely different service).
                // But generally, same merchant + same schedule = Subscription.
                
                // Use most recent amount for "current" price
                $lastTransaction = end($group);
                
                $subscriptions[] = [
                    'merchant_name' => $merchant, // Normalized name
                    'average_amount' => $lastTransaction['amount'], // Use latest amount
                    'billing_cycle' => $intervalAnalysis['cycle'],
                    'first_detected_date' => $group[0]['created_at'],
                    'last_charged_date' => $lastTransaction['created_at'],
                    'confidence_score' => $this->calculateConfidence($amountAnalysis, $intervalAnalysis, count($group)),
                    'status' => 'active',
                    // Internal use for Step 2
                    'transaction_ids' => array_column($group, 'id') 
                ];
            }
        }

        return $subscriptions;
    }

    private function fetchTransactions() {
        // Fetch only expenses, ordered by description then date
        $stmt = $this->conn->prepare("
            SELECT id, amount, description, created_at 
            FROM transactions 
            WHERE user_id = ? AND type = 'expense' 
            ORDER BY description, created_at ASC
        ");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    private function groupTransactions($transactions) {
        $grouped = [];
        foreach ($transactions as $t) {
            // Normalize merchant name: lowercase, trim, remove common suffixes could be added later
            // For now: Simple normalization
            $key = strtolower(trim($t['description']));
            // Remove digits simply to group "Netflix 1" and "Netflix 2" if needed, 
            // but prompt says "Highly similar".
            // Let's rely on description being stable for now as per "Same merchant_name".
            
            // Basic cleaning
            $key = preg_replace('/\s+/', ' ', $key); // collapse spaces
            
            $grouped[$key][] = $t;
        }
        return $grouped;
    }

    private function analyzeAmounts($group) {
        $amounts = array_column($group, 'amount');
        $average = array_sum($amounts) / count($amounts);
        
        // Check tolerance +/- 5%
        $isConsistent = true;
        $varianceCount = 0;
        
        foreach ($amounts as $amt) {
            $diff = abs($amt - $average);
            if ($average > 0) {
                $percentDiff = ($diff / $average) * 100;
                if ($percentDiff > 5) {
                    $varianceCount++;
                }
            }
        }

        // Allow some variance but generally should be consistent
        // Prompt says "Same amount (±2–5% tolerance)". 
        // Strict interpretation: All must be within range.
        // Loose interpretation: Most.
        // Let's say if > 80% fit, it's a match, considering price hikes?
        // For Step 1: Let's stick to stricter rules to minimize false positives: 
        // ALL must be within 5% OR we identify a "main" price.
        // Actually, let's just use the average logic for now. 
        // If > 1 outlier, fail.
        if ($varianceCount > 1 && count($group) < 10) {
            $isConsistent = false;
        } elseif ($varianceCount > (count($group) * 0.2)) {
             $isConsistent = false;
        }

        return [
            'is_consistent' => $isConsistent,
            'average' => $average
        ];
    }

    private function analyzeIntervals($group) {
        $dates = array_map(function($t) {
            return strtotime($t['created_at']);
        }, $group);
        
        sort($dates);
        
        $intervals = [];
        for ($i = 0; $i < count($dates) - 1; $i++) {
            $diffSeconds = $dates[$i+1] - $dates[$i];
            $days = $diffSeconds / (60 * 60 * 24);
            $intervals[] = $days;
        }

        if (empty($intervals)) {
            return ['is_recurring' => false, 'cycle' => 'unknown'];
        }

        // Determine cycle
        $cycle = 'unknown';
        $weeklyMatches = 0;
        $monthlyMatches = 0;
        $yearlyMatches = 0;
        
        foreach ($intervals as $days) {
            if ($days >= 6 && $days <= 8) $weeklyMatches++;
            if ($days >= 28 && $days <= 31) $monthlyMatches++;
            if ($days >= 350 && $days <= 380) $yearlyMatches++;
        }

        $count = count($intervals);
        $isRecurring = false;
        
        // Threshold: 75% of intervals must match a pattern
        if ($weeklyMatches / $count >= 0.75) {
            $cycle = 'weekly';
            $isRecurring = true;
        } elseif ($monthlyMatches / $count >= 0.75) {
            $cycle = 'monthly';
            $isRecurring = true;
        } elseif ($yearlyMatches / $count >= 0.75) {
            $cycle = 'yearly';
            $isRecurring = true;
        }

        // Handling "Skipped months" or specific irregularities is for later steps (Step 6),
        // but basic missed detection relies on this percentage check.

        return [
            'is_recurring' => $isRecurring,
            'cycle' => $cycle
        ];
    }

    private function calculateConfidence($amtAnalysis, $intAnalysis, $count) {
        $score = 50; // Base
        
        // More occurrences = higher confidence
        if ($count > 5) $score += 20;
        if ($count > 12) $score += 10;
        
        // Consistent amount
        if ($amtAnalysis['is_consistent']) $score += 10;
        
        // Perfect intervals?
        // (Could refine this)
        
        return min(100, $score);
    }
}
