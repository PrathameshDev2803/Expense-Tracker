CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    goal_type ENUM('saving', 'no_spend', 'budget') NOT NULL,
    target_amount DECIMAL(15, 2) NULL, -- For Saving and Budget
    target_days INT NULL, -- For No-Spend
    current_amount DECIMAL(15, 2) DEFAULT 0.00, -- For Saving and Budget
    completed_days INT DEFAULT 0, -- For No-Spend
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('active', 'completed', 'failed') DEFAULT 'active',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Example Indexes for performance
CREATE INDEX idx_goals_user_status ON goals(user_id, status);
