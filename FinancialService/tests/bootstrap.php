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

    // Use in-memory SQLite database for tests
    $tempDB = ':memory:';
    putenv("SQLITE_PATH=$tempDB");

    $pdo = new PDO("sqlite:$tempDB");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo ('Creating database schema...' . PHP_EOL);

    // Create all tables using native SQL
    $sqlStatements = [
        // Create wallets table
        "CREATE TABLE wallets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            user_email VARCHAR(150) NOT NULL,
            name VARCHAR(100),
            description TEXT,
            status BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        // Create entry_types table
        "CREATE TABLE entry_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        // Create expense_categories table
        "CREATE TABLE expense_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        // Create payment_methods table
        "CREATE TABLE payment_methods (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        // Create entries table
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

        // Create expenses table
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

        // Create investiment_categories table
        "CREATE TABLE investiment_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        // Create investiment_types table
        "CREATE TABLE investiment_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100)
        )",

        // Create investiments table
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

        // Create investiment_redemptions table
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

        // Create loans table
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

        // Create indexes
        "CREATE INDEX idx_wallets_user_id ON wallets(user_id)",
        "CREATE INDEX idx_wallets_user_email ON wallets(user_email)",
        "CREATE INDEX idx_entries_month ON entries(month)",
        "CREATE INDEX idx_entries_year ON entries(year)",
        "CREATE INDEX idx_expenses_month ON expenses(month)",
        "CREATE INDEX idx_expenses_year ON expenses(year)",

        // Insert seed data for expense_categories
        "INSERT INTO expense_categories (name) VALUES ('Alimentação')",
        "INSERT INTO expense_categories (name) VALUES ('Transporte')",
        "INSERT INTO expense_categories (name) VALUES ('Investimento')",
        "INSERT INTO expense_categories (name) VALUES ('Empréstimo')",
        "INSERT INTO expense_categories (name) VALUES ('Educação')",
        "INSERT INTO expense_categories (name) VALUES ('Lazer')",
        "INSERT INTO expense_categories (name) VALUES ('Saúde')",
        "INSERT INTO expense_categories (name) VALUES ('Moradia')",
        "INSERT INTO expense_categories (name) VALUES ('Serviços')",
        "INSERT INTO expense_categories (name) VALUES ('Outros')",
        "INSERT INTO expense_categories (name) VALUES ('Igreja')",
        "INSERT INTO expense_categories (name) VALUES ('Fatura Cartão')",
        "INSERT INTO expense_categories (name) VALUES ('Fatura Celular')",
        "INSERT INTO expense_categories (name) VALUES ('Mercado')",
        "INSERT INTO expense_categories (name) VALUES ('Itens de Higiene')",
        "INSERT INTO expense_categories (name) VALUES ('Conta de Luz')",
        "INSERT INTO expense_categories (name) VALUES ('Conta de Água')",
        "INSERT INTO expense_categories (name) VALUES ('Conta de Internet')",
        "INSERT INTO expense_categories (name) VALUES ('Compras na Internet')",
        "INSERT INTO expense_categories (name) VALUES ('Gasolina')",
        "INSERT INTO expense_categories (name) VALUES ('Presentes')",

        // Insert seed data for entry_types
        "INSERT INTO entry_types (name) VALUES ('Salário')",
        "INSERT INTO entry_types (name) VALUES ('Freelance')",
        "INSERT INTO entry_types (name) VALUES ('Investimento')",
        "INSERT INTO entry_types (name) VALUES ('Presente')",
        "INSERT INTO entry_types (name) VALUES ('Outros')",

        // Insert seed data for payment_methods
        "INSERT INTO payment_methods (name) VALUES ('Dinheiro')",
        "INSERT INTO payment_methods (name) VALUES ('Cartão de Crédito')",
        "INSERT INTO payment_methods (name) VALUES ('Cartão de Débito')",
        "INSERT INTO payment_methods (name) VALUES ('PIX')",
        "INSERT INTO payment_methods (name) VALUES ('Transferência')",
        "INSERT INTO payment_methods (name) VALUES ('Boleto')",

        // Insert seed data for investiment_categories
        "INSERT INTO investiment_categories (name) VALUES ('Ações')",
        "INSERT INTO investiment_categories (name) VALUES ('Fundos')",
        "INSERT INTO investiment_categories (name) VALUES ('Tesouro Direto')",
        "INSERT INTO investiment_categories (name) VALUES ('CDB')",
        "INSERT INTO investiment_categories (name) VALUES ('LCI/LCA')",
        "INSERT INTO investiment_categories (name) VALUES ('Criptomoedas')",
        "INSERT INTO investiment_categories (name) VALUES ('Outros')",

        // Insert seed data for investiment_types
        "INSERT INTO investiment_types (name) VALUES ('Compra')",
        "INSERT INTO investiment_types (name) VALUES ('Venda')",
        "INSERT INTO investiment_types (name) VALUES ('Dividendos')",
        "INSERT INTO investiment_types (name) VALUES ('Juros')",
        "INSERT INTO investiment_types (name) VALUES ('Outros')"
    ];

    // Execute all SQL statements
    foreach ($sqlStatements as $sql) {
        $pdo->exec($sql);
    }

    echo ('Database schema created successfully with seed data.' . PHP_EOL);
    echo ('Total tables created: ' . $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->rowCount() . PHP_EOL);
});
