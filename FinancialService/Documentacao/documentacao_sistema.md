Claro! Abaixo está a **documentação completa e final** do seu **Sistema de Finanças Pessoais**, agora com a tabela `months` renomeada para `month_controls`, refletindo com mais clareza seu papel de **controle financeiro mensal**.

---

# 📘 Documentação Completa — Sistema de Finanças Pessoais

---

## 🎯 Objetivos do Sistema

O sistema visa fornecer um controle financeiro pessoal estruturado, com foco em organização, rastreabilidade e flexibilidade de análises. Os principais objetivos incluem:

1. **Centralizar o controle financeiro pessoal**, com lançamentos mensais de entradas, despesas, investimentos e empréstimos vinculados a uma carteira única por usuário.

2. **Organizar os registros por mês de referência**, utilizando a tabela `month_controls`, que representa o controle financeiro de um determinado mês e ano.

3. **Separar os saldos por origem** (salário, vale-alimentação, saldo anterior) e registrar as saídas com forma de pagamento e local de gasto.

4. **Gerenciar investimentos como despesas**, com rastreio de tipo e status. Quando resgatados, os valores retornam ao saldo mensal como entradas automáticas.

5. **Gerenciar empréstimos realizados**, com status de aberto/pago. O valor devolvido entra automaticamente como entrada no mês do pagamento.

6. **Permitir a ativação/inativação da carteira**, sem perder o histórico financeiro.

7. **Gerar relatórios e dashboards analíticos**, com agrupamentos por mês, tipo de entrada, categoria de despesa, forma de pagamento, status de investimento e retorno de empréstimos.

8. **Facilitar a inserção automática de controles mensais**, para os meses restantes ou para um ano completo, evitando a criação manual contínua.

---

## 📐 Arquitetura Geral

```
users ──┬── 1:1 ──> wallets (status) ──┬──> month_controls (month, year) ──┬──> entries ─┬──> entry_types
        │                             │                                   └──> expenses ┬──> expense_categories
        │                             │                                                ├──> payment_methods
        │                             │                                                ├──> investments ──┬──> investment_types
        │                             │                                                │                  └──> investment_redemptions → entries
        │                             │                                                └──> loans ────────┬──> loan_returns → entries
        └──> relatórios e dashboards
```

---

## 🧾 Tabelas Principais

---

### 👤 `users`

| Campo       | Tipo         | Descrição                  |
| ----------- | ------------ | -------------------------- |
| id          | INT (PK)     | Identificador do usuário   |
| email       | VARCHAR(150) | E-mail único               |
| name        | VARCHAR(100) | Nome do usuário (opcional) |
| password    | VARCHAR(255) | Senha criptografada        |
| created\_at | DATETIME     | Data de criação            |

---

### 👛 `wallets`

| Campo       | Tipo             | Descrição                                 |
| ----------- | ---------------- | ----------------------------------------- |
| id          | INT (PK)         | Identificador da carteira                 |
| user\_id    | INT (FK, UNIQUE) | Cada usuário pode ter apenas uma carteira |
| user\_email | VARCHAR(150)     | Redundância útil para consultas rápidas   |
| name        | VARCHAR(100)     | Nome da carteira                          |
| description | TEXT             | Descrição (opcional)                      |
| status      | ENUM             | 'ativa', 'inativa'                        |
| created\_at | DATETIME         | Registro criado em                        |

---

### 📅 `month_controls`

| Campo       | Tipo     | Descrição                        |
| ----------- | -------- | -------------------------------- |
| id          | INT (PK) | Identificador do controle mensal |
| wallet\_id  | INT (FK) | Referência à carteira            |
| month       | INT      | Mês (1–12)                       |
| year        | INT      | Ano (ex: 2025)                   |
| created\_at | DATETIME | Registro criado em               |

---

### ➕ `entries`

| Campo              | Tipo          | Descrição              |
| ------------------ | ------------- | ---------------------- |
| id                 | INT (PK)      | Identificador          |
| month\_control\_id | INT (FK)      | Controle do mês        |
| entry\_type\_id    | INT (FK)      | Tipo da entrada        |
| amount             | DECIMAL(10,2) | Valor recebido         |
| description        | TEXT          | Observações (opcional) |
| date               | DATE          | Data da entrada        |
| created\_at        | DATETIME      | Registro criado em     |

---

### ➖ `expenses`

| Campo               | Tipo          | Descrição                      |
| ------------------- | ------------- | ------------------------------ |
| id                  | INT (PK)      | Identificador                  |
| month\_control\_id  | INT (FK)      | Controle do mês                |
| category\_id        | INT (FK)      | Categoria                      |
| payment\_method\_id | INT (FK)      | Forma de pagamento             |
| amount              | DECIMAL(10,2) | Valor gasto                    |
| location            | TEXT          | Local onde foi feita a despesa |
| description         | TEXT          | Observações (opcional)         |
| date                | DATE          | Data da despesa                |
| created\_at         | DATETIME      | Registro criado em             |

---

## 📈 Investimentos

### 📁 `investments`

| Campo                | Tipo     | Descrição                            |
| -------------------- | -------- | ------------------------------------ |
| id                   | INT (PK) | Identificador                        |
| expense\_id          | INT (FK) | Despesa com categoria "investimento" |
| investment\_type\_id | INT (FK) | Tipo do investimento                 |
| status               | ENUM     | 'ativo', 'resgatado'                 |
| created\_at          | DATETIME | Registro criado em                   |

---

### 📁 `investment_redemptions`

| Campo          | Tipo     | Descrição                                 |
| -------------- | -------- | ----------------------------------------- |
| id             | INT (PK) | Identificador                             |
| investment\_id | INT (FK) | Investimento resgatado                    |
| entry\_id      | INT (FK) | Entrada correspondente ao valor retornado |
| date           | DATE     | Data do resgate                           |
| created\_at    | DATETIME | Registro criado em                        |

---

## 🤝 Empréstimos

### 📁 `loans`

| Campo        | Tipo         | Descrição                                     |
| ------------ | ------------ | --------------------------------------------- |
| id           | INT (PK)     | Identificador                                 |
| expense\_id  | INT (FK)     | Despesa com categoria "empréstimo"            |
| person\_name | VARCHAR(150) | Nome da pessoa que recebeu o valor emprestado |
| status       | ENUM         | 'aberto', 'pago'                              |
| created\_at  | DATETIME     | Registro criado em                            |

---

### 📁 `loan_returns`

| Campo       | Tipo     | Descrição                                 |
| ----------- | -------- | ----------------------------------------- |
| id          | INT (PK) | Identificador                             |
| loan\_id    | INT (FK) | Empréstimo quitado                        |
| entry\_id   | INT (FK) | Entrada correspondente ao valor devolvido |
| date        | DATE     | Data do pagamento                         |
| created\_at | DATETIME | Registro criado em                        |

---

## 🧩 Tabelas Auxiliares

### `entry_types`

| id | name                 |
| -- | -------------------- |
| 1  | Salário              |
| 2  | Vale Alimentação     |
| 3  | Saldo Anterior       |
| 4  | Resgate Investimento |
| 5  | Devolução Empréstimo |

---

### `expense_categories`

| id | name         |
| -- | ------------ |
| 1  | Alimentação  |
| 2  | Transporte   |
| 3  | Investimento |
| 4  | Empréstimo   |

---

### `payment_methods`

| id | name             |
| -- | ---------------- |
| 1  | Dinheiro         |
| 2  | Vale Alimentação |
| 3  | Cartão Crédito   |
| 4  | Cartão Débito    |
| 5  | PIX              |

---

### `investment_types`

| id | name           |
| -- | -------------- |
| 1  | Renda Fixa     |
| 2  | Ações          |
| 3  | Cripto         |
| 4  | Tesouro Direto |

---

## 📌 Extra: Inserção automática de meses via PHP

```php
function insertMonthsForYear(PDO $db, int $walletId, int $year, bool $onlyRemaining = true): void
{
    $currentMonth = (int)date('n');
    $values = [];

    for ($m = 1; $m <= 12; $m++) {
        if ($onlyRemaining && $m < $currentMonth) continue;
        $values[] = "($walletId, $m, $year, NOW())";
    }

    if (!empty($values)) {
        $sql = "INSERT IGNORE INTO month_controls (wallet_id, month, year, created_at) VALUES " . implode(",", $values);
        $db->exec($sql);
    }
}
```

---

Se quiser agora, posso gerar:

* Script SQL com todos os `CREATE TABLE`
* Exportar esta documentação em `.pdf`, `.docx` ou `.md`

Deseja algum desses formatos?
