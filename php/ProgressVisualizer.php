<?php

class ProgressVisualizer {

    /**
     * Renders a Horizontal Progress Bar.
     * Best for: Monthly budget tracking.
     * 
     * @param array $goal Goal data [title, current, target, type]
     * @param string $size 'sm', 'md', 'lg'
     */
    public static function renderHorizontalBar($goal, $size = 'md') {
        $percent = self::calculatePercent($goal['current'], $goal['target']);
        $colorState = self::getColorState($percent, $goal['type'] ?? 'budget');
        $displayPercent = round($percent);
        $id = 'pv-bar-' . uniqid();

        // Calculate Remaining
        $remaining = $goal['target'] - $goal['current'];
        $remainingText = $remaining > 0 ? "Remaining: " . number_format($remaining) : "Goal Reached!";

        echo "
        <div class='pv-container pv-size-$size' id='$id' data-pv-type='bar' data-percent='$percent'>
            <div class='pv-header'>
                <span class='pv-title'>{$goal['title']}</span>
                <span class='pv-value'>{$displayPercent}%</span>
            </div>
            <div class='pv-bar-track'>
                <div class='pv-bar-fill' data-state='$colorState' style='width: 0%'></div>
            </div>
             <div class='pv-feedback-toast' id='$id-toast'></div>
            <div style='font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: flex; justify-content: space-between;'>
                <span>{$goal['current']} / {$goal['target']}</span>
                <span>$remainingText</span>
            </div>
        </div>
        ";
    }

    /**
     * Renders a Circular Progress Ring.
     * Best for: Savings & No-Spend.
     * 
     * @param array $goal Goal data [title, current, target]
     */
    public static function renderCircularRing($goal) {
        $percent = self::calculatePercent($goal['current'], $goal['target']);
        $displayPercent = round($percent);
        $radius = 54;
        $circumference = 2 * pi() * $radius; // ~339.292
        $id = 'pv-ring-' . uniqid();

        echo "
        <div class='pv-container' style='align-items: center;' id='$id' data-pv-type='ring' data-percent='$percent' data-circumference='$circumference'>
            <div class='pv-ring-wrapper'>
                <svg class='pv-ring-svg' viewBox='0 0 120 120'>
                    <circle class='pv-ring-circle-bg' cx='60' cy='60' r='$radius'></circle>
                    <circle class='pv-ring-circle-fg' cx='60' cy='60' r='$radius' stroke-dasharray='$circumference' stroke-dashoffset='$circumference'></circle>
                </svg>
                <div class='pv-ring-content'>
                    <span class='pv-ring-percentCount' data-target='$displayPercent'>0</span><span class='pv-ring-percent'>%</span>
                    <span class='pv-ring-label'>{$goal['title']}</span>
                </div>
                <div class='pv-feedback-toast' id='$id-toast'></div>
            </div>
        </div>
        ";
    }

    /**
     * Renders a Mini Sparkline Chart.
     * Best for: Progress over time.
     * 
     * @param array $goal Goal data with 'history' array [val1, val2, ...]
     */
    public static function renderSparkline($goal) {
        $data = $goal['history'] ?? [];
        if (empty($data)) return;

        // SVG Size
        $width = 300;
        $height = 60;
        $max = max($data) ?: 1; // Avoid div by zero
        $min = min($data);
        
        // Generate Points
        $points = "";
        $stepX = $width / (count($data) - 1);
        
        foreach ($data as $i => $val) {
            $x = $i * $stepX;
            // Invert Y (SVG 0 is top)
            // Normalize val between 0 and height (with some padding)
            $y = $height - (($val / $max) * ($height - 10)); // 10px padding top
            $points .= "$x,$y ";
        }

        echo "
        <div class='pv-container'>
            <div class='pv-header'>
                <span class='pv-title'>{$goal['title']}</span>
            </div>
            <div class='pv-sparkline-wrapper'>
                <svg class='pv-sparkline-svg' viewBox='0 0 $width $height' preserveAspectRatio='none'>
                    <defs>
                        <linearGradient id='gradient-fill' x1='0' x2='0' y1='0' y2='1'>
                            <stop offset='0%' stop-color='var(--prog-brand)' />
                            <stop offset='100%' stop-color='transparent' />
                        </linearGradient>
                    </defs>
                    <polyline points='$points' class='pv-sparkline-path'></polyline>
                </svg>
            </div>
        </div>
        ";
    }

    // --- Helpers ---

    private static function calculatePercent($current, $target) {
        if ($target <= 0) return 0;
        $p = ($current / $target) * 100;
        return max(0, min(100, $p)); // Clamp 0-100
    }

    private static function getColorState($percent, $type) {
        if ($type === 'budget') {
            // High % means less budget remaining -> Danger
            if ($percent > 85) return 'danger';
            if ($percent > 60) return 'warning';
            return 'neutral'; // Or success/cool color
        } else {
            // Saving/No-Spend: High % is good
            if ($percent >= 100) return 'success';
            if ($percent > 50) return 'neutral'; // Blue/Brand
            return 'neutral';
        }
    }
}
?>
