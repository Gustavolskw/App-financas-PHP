# Documentação do Sistema de Finanças Pessoais Em SQL
### 📘 1. `users`

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL UNIQUE,
    name VARCHAR(100),
    password VARCHAR(255),
    created_at DATETIME NOT NULL
);
```

---

### 👛 2. `wallets`

```sql
CREATE TABLE wallets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL UNIQUE,
    user_email VARCHAR(150) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status TINYINT(1) DEFAULT 1, -- 1 = ativa, 0 = inativa
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

### 📅 3. `month_controls`

```sql
CREATE TABLE month_controls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    wallet_id BIGINT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE (wallet_id, month, year),
    FOREIGN KEY (wallet_id) REFERENCES wallets(id)
);
```

---

### 🧩 4. `entry_types`

```sql
CREATE TABLE entry_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);
```

---

### ➕ 5. `entries`

```sql
CREATE TABLE entries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    month_control_id BIGINT NOT NULL,
    entry_type_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (month_control_id) REFERENCES month_controls(id),
    FOREIGN KEY (entry_type_id) REFERENCES entry_types(id)
);
```

---

### 🧾 6. `expense_categories`

```sql
CREATE TABLE expense_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);
```

---

### 💳 7. `payment_methods`

```sql
CREATE TABLE payment_methods (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);
```

---

### ➖ 8. `expenses`

```sql
CREATE TABLE expenses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    month_control_id BIGINT NOT NULL,
    category_id BIGINT NOT NULL,
    payment_method_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    location TEXT,
    description TEXT,
    date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (month_control_id) REFERENCES month_controls(id),
    FOREIGN KEY (category_id) REFERENCES expense_categories(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);
```

---

### 💼 9. `investment_types`

```sql
CREATE TABLE investment_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);
```

---

### 📈 10. `investments`

```sql
CREATE TABLE investments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    expense_id BIGINT NOT NULL,
    investment_type_id BIGINT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1, -- 1 = ativo, 0 = resgatado
    created_at DATETIME NOT NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id),
    FOREIGN KEY (investment_type_id) REFERENCES investment_types(id)
);
```

---

### 💸 11. `investment_redemptions`

```sql
CREATE TABLE investment_redemptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    investment_id BIGINT NOT NULL,
    entry_id BIGINT NOT NULL,
    date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (investment_id) REFERENCES investments(id),
    FOREIGN KEY (entry_id) REFERENCES entries(id)
);
```

---

### 🤝 12. `loans`

```sql
CREATE TABLE loans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    expense_id BIGINT NOT NULL,
    person_name VARCHAR(150) NOT NULL,
    status TINYINT(1) DEFAULT 0, -- 0 = aberto, 1 = pago
    created_at DATETIME NOT NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id)
);
```

---

### 💵 13. `loan_returns`

```sql
CREATE TABLE loan_returns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    loan_id BIGINT NOT NULL,
    entry_id BIGINT NOT NULL,
    date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    FOREIGN KEY (entry_id) REFERENCES entries(id)
);
```

---
