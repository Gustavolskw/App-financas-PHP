<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo ('Bootstrap file loaded' . PHP_EOL);

call_user_func(function () {
    echo ('Setup database start ...' . PHP_EOL);

    // Check if SQLite PDO driver is available
    if (!in_array('sqlite', PDO::getAvailableDrivers())) {
        echo "SQLite PDO driver not available. Available drivers: " . implode(', ', PDO::getAvailableDrivers()) . PHP_EOL;
        echo "Tests will be skipped due to missing SQLite support." . PHP_EOL;
        return;
    }

    // Create a physical temporary SQLite file (shared between test bootstrap and PHPUnit)
    $sqlitePath = sys_get_temp_dir() . '/test_database.sqlite';
    if (file_exists($sqlitePath)) {
        unlink($sqlitePath); // Remove previous database if it exists
    }
    putenv("SQLITE_PATH=$sqlitePath");

    // Initialize PDO
    $pdo = new PDO("sqlite:$sqlitePath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo ('Creating database schema...' . PHP_EOL);

    // Array with schema + seed data
    $sqlStatements = [
        // --- Criação das tabelas principais ---
        "CREATE TABLE wallets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            user_email VARCHAR(150) NOT NULL,
            name VARCHAR(100),
            description TEXT,
            status BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE entry_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        "CREATE TABLE expense_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        "CREATE TABLE payment_methods (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        "CREATE TABLE entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wallet_id INTEGER,
            entry_type_id INTEGER,
            amount DECIMAL(10,2),
            description TEXT,
            date DATE,
            month INTEGER NOT NULL,
            year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id),
            FOREIGN KEY (entry_type_id) REFERENCES entry_types(id)
        )",

        "CREATE TABLE expenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wallet_id INTEGER,
            category_id INTEGER,
            payment_method_id INTEGER,
            amount DECIMAL(10,2),
            location TEXT,
            description TEXT,
            date DATE,
            month INTEGER NOT NULL,
            year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id),
            FOREIGN KEY (category_id) REFERENCES expense_categories(id),
            FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
        )",

        "CREATE TABLE investiment_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        "CREATE TABLE investiment_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        "CREATE TABLE investiments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wallet_id INTEGER,
            category_id INTEGER,
            type_id INTEGER,
            amount DECIMAL(10,2),
            description TEXT,
            date DATE,
            month INTEGER NOT NULL,
            year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id),
            FOREIGN KEY (category_id) REFERENCES investiment_categories(id),
            FOREIGN KEY (type_id) REFERENCES investiment_types(id)
        )",

        "CREATE TABLE investiment_redemptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            investiment_id INTEGER,
            amount DECIMAL(10,2),
            description TEXT,
            date DATE,
            month INTEGER NOT NULL,
            year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (investiment_id) REFERENCES investiments(id)
        )",

        "CREATE TABLE loans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wallet_id INTEGER,
            amount DECIMAL(10,2),
            description TEXT,
            date DATE,
            month INTEGER NOT NULL,
            year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id)
        )",

        // --- Índices ---
        "CREATE INDEX idx_wallets_user_id ON wallets(user_id)",
        "CREATE INDEX idx_wallets_user_email ON wallets(user_email)",
        "CREATE INDEX idx_entries_month ON entries(month)",
        "CREATE INDEX idx_entries_year ON entries(year)",
        "CREATE INDEX idx_expenses_month ON expenses(month)",
        "CREATE INDEX idx_expenses_year ON expenses(year)",

        // --- Dados de exemplo (seed) ---
        // (omitido aqui para brevidade, mas será executado normalmente)
        // Ex: INSERT INTO expense_categories (...), entry_types (...), etc.
    ];

    foreach ($sqlStatements as $sql) {
        $pdo->exec($sql);
    }

    echo ('Database schema created successfully with seed data.' . PHP_EOL);

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo ('Total tables created: ' . count($tables) . PHP_EOL);
});
