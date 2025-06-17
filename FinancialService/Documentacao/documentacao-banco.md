# Personal Finance Control Database Schema

## Overview
Simple and straightforward database design for personal monthly financial control. Each user has a single wallet to manage their personal finances with income/expense tracking and monthly budgeting capabilities.

## Database Creation

```sql
CREATE DATABASE IF NOT EXISTS financial_control 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE financial_control;
```

## Table Structure

### 1. Categories Table
**Functionality**: Stores predefined categories for income and expenses (e.g., Salary, Food, Transport). Helps organize and classify transactions for better financial analysis.

```sql
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(7) NULL COMMENT 'Hexadecimal color (#FFFFFF)',
    icon VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB COMMENT='Income and expense categories';
```

### 2. Wallets Table
**Functionality**: Represents each user's personal wallet/account. One wallet per user acts as their main financial container where all transactions are recorded. Includes dual validation with both user_id and user_email for enhanced security. Balance is calculated entirely from transactions.

```sql
CREATE TABLE wallets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE COMMENT 'User ID from auth microservice (unique per user)',
    user_email VARCHAR(255) NOT NULL UNIQUE COMMENT 'User email for dual validation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_user_email (user_email),
    UNIQUE KEY uk_user_validation (user_id, user_email)
) ENGINE=InnoDB COMMENT='Single wallet per user with dual validation';
```

### 3. Transactions Table
**Functionality**: Core table that records all financial movements (income and expenses). Each transaction is linked to a category and wallet, with a simple boolean to distinguish between income (true) and expense (false).

```sql
CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    is_income BOOLEAN NOT NULL COMMENT 'true for income, false for expense',
    transaction_date DATE NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_wallet_id (wallet_id),
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_is_income (is_income),
    INDEX idx_date (transaction_date),
    INDEX idx_wallet_date (wallet_id, transaction_date),
    INDEX idx_user_month (user_id, YEAR(transaction_date), MONTH(transaction_date)),
    
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Financial transactions';
```

### 4. Monthly Budgets Table
**Functionality**: Allows users to set spending limits for each category per month. Helps with financial planning and expense control by defining monthly goals for different expense categories.

```sql
CREATE TABLE monthly_budgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    
    budgeted_amount DECIMAL(15,2) NOT NULL,
    month TINYINT UNSIGNED NOT NULL COMMENT '1-12',
    year YEAR NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_period (year, month),
    
    UNIQUE KEY uk_user_category_period (user_id, category_id, year, month),
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Monthly budget per category';
```

## Initial Data - Default Categories

### Income Categories
```sql
INSERT INTO categories (name, type, color, icon) VALUES
('Salary', 'income', '#28a745', 'salary'),
('Freelance', 'income', '#17a2b8', 'freelance'),
('Investments', 'income', '#6f42c1', 'investment'),
('Sales', 'income', '#fd7e14', 'sales'),
('Other Income', 'income', '#6c757d', 'other');
```

### Expense Categories
```sql
INSERT INTO categories (name, type, color, icon) VALUES
('Food', 'expense', '#dc3545', 'food'),
('Transport', 'expense', '#007bff', 'transport'),
('Housing', 'expense', '#28a745', 'home'),
('Health', 'expense', '#e83e8c', 'health'),
('Education', 'expense', '#6f42c1', 'education'),
('Entertainment', 'expense', '#fd7e14', 'entertainment'),
('Clothing', 'expense', '#20c997', 'clothing'),
('Bills/Taxes', 'expense', '#6c757d', 'bills'),
('Other Expenses', 'expense', '#343a40', 'other');
```

## Common Queries for API Development

### Get wallet information with dual validation
```sql
SELECT id, created_at 
FROM wallets 
WHERE user_id = ? AND user_email = ?;
```

### Calculate current balance (from transactions only)
```sql
SELECT 
    COALESCE(
        SUM(CASE WHEN is_income = 1 THEN amount ELSE -amount END), 0
    ) as current_balance
FROM transactions 
WHERE wallet_id = ?;
```

### Get monthly income
```sql
SELECT SUM(amount) as total_income
FROM transactions 
WHERE wallet_id = ? 
  AND is_income = 1
  AND YEAR(transaction_date) = ? 
  AND MONTH(transaction_date) = ?;
```

### Get monthly expenses
```sql
SELECT SUM(amount) as total_expenses
FROM transactions 
WHERE wallet_id = ? 
  AND is_income = 0
  AND YEAR(transaction_date) = ? 
  AND MONTH(transaction_date) = ?;
```

### Get expenses by category (current month)
```sql
SELECT c.name, c.color, SUM(t.amount) as spent
FROM transactions t 
JOIN categories c ON t.category_id = c.id 
WHERE t.wallet_id = ? 
  AND t.is_income = 0
  AND YEAR(t.transaction_date) = YEAR(CURDATE())
  AND MONTH(t.transaction_date) = MONTH(CURDATE())
GROUP BY c.id, c.name, c.color;
```

### Get budget vs actual spending
```sql
SELECT 
    c.name,
    mb.budgeted_amount,
    COALESCE(SUM(t.amount), 0) as spent_amount,
    (mb.budgeted_amount - COALESCE(SUM(t.amount), 0)) as remaining
FROM monthly_budgets mb
JOIN categories c ON mb.category_id = c.id
LEFT JOIN transactions t ON t.category_id = mb.category_id 
    AND t.user_id = mb.user_id
    AND YEAR(t.transaction_date) = mb.year
    AND MONTH(t.transaction_date) = mb.month
    AND t.is_income = 0
WHERE mb.user_id = ? 
  AND mb.year = ? 
  AND mb.month = ?
GROUP BY mb.id, c.name, mb.budgeted_amount;
```

### Get recent transactions
```sql
SELECT 
    t.id,
    t.description,
    t.amount,
    t.is_income,
    t.transaction_date,
    c.name as category_name,
    c.color as category_color
FROM transactions t
JOIN categories c ON t.category_id = c.id
WHERE t.wallet_id = ?
ORDER BY t.transaction_date DESC, t.created_at DESC
LIMIT 20;
```

## API Integration Notes

1. **User Wallet Creation**: When a new user registers in your auth microservice, automatically create a wallet entry with both user_id and user_email
2. **Dual Validation**: Always validate operations using both user_id AND user_email for enhanced security
3. **Transaction-Based Balance**: All balance calculations are done entirely from transactions - no stored balance fields
4. **Monthly Reports**: Use the provided queries to generate monthly financial reports
5. **Budget Tracking**: Compare budgeted amounts with actual spending for budget alerts

## Database Features

- **Simple Structure**: Only 4 tables for easy maintenance
- **One Wallet Per User**: Simplified financial management
- **Monthly Focus**: Designed for monthly financial control
- **Categorized Transactions**: Better financial analysis and reporting
- **Budget Control**: Monthly spending limits per category
- **Optimized Indexes**: Fast queries for common operations
- **Foreign Key Constraints**: Data integrity protection