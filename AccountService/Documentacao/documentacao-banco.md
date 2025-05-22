## 💼 **Modelo de Banco de Dados — Finanças Pessoais**

---

### 🧾 Tabela: `users`

#### 📄 SQL

```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Usuários do sistema, contendo:

* Nome, e-mail (único) e senha criptografada.
* Data de criação do cadastro.

#### 🧩 Função

Identifica quem está usando o sistema. Todas as contas e dados financeiros pertencem a um usuário.

---

### 🧾 Tabela: `accounts`

#### 📄 SQL

```sql
CREATE TABLE accounts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    initial_balance DECIMAL(12,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Contas financeiras (banco, carteira etc.):

* Tipo e saldo inicial.
* Moeda e status ativo/inativo.

#### 🧩 Função

Organiza separadamente os saldos e transações de um usuário.

---

### 🧾 Tabela: `categories`

#### 📄 SQL

```sql
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type BOOLEAN NOT NULL, -- TRUE = receita, FALSE = despesa
    color VARCHAR(7),
    icon VARCHAR(100),
    is_custom BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Categorias de transações:

* Compartilhadas entre todos os usuários.
* Tipo `TRUE` (receita), `FALSE` (despesa).

#### 🧩 Função

Permite classificar transações para relatórios e organização.

---

### 🧾 Tabela: `transactions`

#### 📄 SQL

```sql
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    account_id INTEGER REFERENCES accounts(id),
    category_id INTEGER REFERENCES categories(id),
    description TEXT,
    amount DECIMAL(12,2) NOT NULL,
    type BOOLEAN NOT NULL, -- TRUE = receita, FALSE = despesa
    date DATE NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Registro de entradas e saídas:

* Vinculadas a uma conta e categoria.
* Inclui valor, descrição e recorrência.

#### 🧩 Função

É a base de todo controle financeiro. Permite montar extratos, relatórios e controle de saldo.

---

### 🧾 Tabela: `financial_goals`

#### 📄 SQL

```sql
CREATE TABLE financial_goals (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    current_amount DECIMAL(12,2) DEFAULT 0.00,
    due_date DATE,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Metas de economia ou planejamento financeiro:

* Valor-alvo, progresso e data-limite.

#### 🧩 Função

Ajuda o usuário a organizar seus objetivos e visualizar avanços.

---

### 🧾 Tabela: `budgets`

#### 📄 SQL

```sql
CREATE TABLE budgets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    category_id INTEGER REFERENCES categories(id),
    limit_amount DECIMAL(12,2) NOT NULL,
    month INTEGER CHECK (month BETWEEN 1 AND 12),
    year INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 📘 Explicação

Orçamentos mensais por categoria:

* Limites de gasto por mês e ano.

#### 🧩 Função

Permite definir e acompanhar limites de gastos mensais por categoria.

---

### 🧾 Tabela: `user_settings`

#### 📄 SQL

```sql
CREATE TABLE user_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    key VARCHAR(100) NOT NULL,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, key)
);
```

#### 📘 Explicação

Configurações personalizadas:

* Exemplo: moeda padrão, tema, idioma.

#### 🧩 Função

Oferece customização por usuário, sem modificar a estrutura geral do sistema.

---

### 🧾 Tabela: `currencies`

#### 📄 SQL

```sql
CREATE TABLE currencies (
    code CHAR(3) PRIMARY KEY,
    name VARCHAR(50),
    symbol VARCHAR(10),
    exchange_rate_to_usd DECIMAL(12,6)
);
```

#### 📘 Explicação

Lista de moedas disponíveis:

* Com símbolo e taxa de conversão para USD.

#### 🧩 Função

Permite múltiplas moedas por conta e exibição adequada dos saldos.

---

Se desejar, posso gerar os scripts de **criação em arquivo `.sql`**, **popular as tabelas com dados iniciais** ou **prototipar a interface/API** com base nesse modelo. Deseja algum desses próximos passos?
