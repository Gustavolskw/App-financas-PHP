
## ✅ Estrutura das Tabelas

---

### `users`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('users');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('email', 'string', ['length' => 150, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 100, 'notnull' => false]);
        $table->addColumn('password', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'uniq_user_email');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
}
```

---

### `wallets`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create wallets table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('wallets');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('user_id', 'bigint', ['notnull' => true]);
        $table->addColumn('user_email', 'string', ['length' => 150, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['user_id'], 'uniq_user_id');
        $table->addIndex(['user_email'], 'idx_user_email');
        $table->addForeignKeyConstraint('users', ['user_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('wallets');
    }
}
```

---

### `month_controls`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create month_controls table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('month_controls');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint');
        $table->addColumn('month', 'integer');
        $table->addColumn('year', 'integer');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['wallet_id', 'month', 'year']);
        $table->addForeignKeyConstraint('wallets', ['wallet_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('month_controls');
    }
}
```

---

### `entry_types`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entry_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('entry_types');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('entry_types');
    }
}
```

---

### `entries`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entries table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('entries');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('month_control_id', 'bigint');
        $table->addColumn('entry_type_id', 'bigint');
        $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('date', 'date');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('month_controls', ['month_control_id'], ['id']);
        $table->addForeignKeyConstraint('entry_types', ['entry_type_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('entries');
    }
}
```

---

### `expense_categories`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create expense_categories table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('expense_categories');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('expense_categories');
    }
}
```

---

### `payment_methods`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment_methods table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('payment_methods');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('payment_methods');
    }
}
```

---

### `expenses`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create expenses table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('expenses');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('month_control_id', 'bigint');
        $table->addColumn('category_id', 'bigint');
        $table->addColumn('payment_method_id', 'bigint');
        $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
        $table->addColumn('location', 'text');
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('date', 'date');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('month_controls', ['month_control_id'], ['id']);
        $table->addForeignKeyConstraint('expense_categories', ['category_id'], ['id']);
        $table->addForeignKeyConstraint('payment_methods', ['payment_method_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('expenses');
    }
}
```

---

### `investment_types`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investment_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investment_types');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investment_types');
    }
}
```

---

### `investments`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609100900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investments table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investments');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('expense_id', 'bigint');
        $table->addColumn('investment_type_id', 'bigint');
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('expenses', ['expense_id'], ['id']);
        $table->addForeignKeyConstraint('investment_types', ['investment_type_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investments');
    }
}
```

---

### `investment_redemptions`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investment_redemptions table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investment_redemptions');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('investment_id', 'bigint');
        $table->addColumn('entry_id', 'bigint');
        $table->addColumn('date', 'date');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('investments', ['investment_id'], ['id']);
        $table->addForeignKeyConstraint('entries', ['entry_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investment_redemptions');
    }
}
```

---

### `loans`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609101100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create loans table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('loans');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('expense_id', 'bigint');
        $table->addColumn('person_name', 'string', ['length' => 150]);
        $table->addColumn('status', 'boolean', ['default' => false]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('expenses', ['expense_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('loans');
    }
}
```

---

### `loan_returns`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609101200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create loan_returns table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('loan_returns');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('loan_id', 'bigint');
        $table->addColumn('entry_id', 'bigint');
        $table->addColumn('date', 'date');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('loans', ['loan_id'], ['id']);
        $table->addForeignKeyConstraint('entries', ['entry_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('loan_returns');
    }
}
```

---

## 📥 Migrations de Dados Predefinidos

---

### `entry_types`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for entry_types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Salário')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Vale Alimentação')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Saldo Anterior')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Resgate Investimento')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Devolução Empréstimo')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM entry_types");
    }
}
```

---

### `expense_categories`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609110100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for expense_categories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Alimentação')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Transporte')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Investimento')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Empréstimo')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Educação')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Lazer')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Saúde')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Moradia')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Serviços')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Outros')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Igreja')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Fatura Cartão')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Fatura Celular')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Mercado')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Itens de Higiene')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Luz')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Água')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Internet')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Compras na Internet')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Gasolina')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Presentes')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM expense_categories");
    }
}
```

---

### `payment_methods`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609110200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for payment_methods';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Dinheiro')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Vale Alimentação')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Cartão Crédito')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Cartão Débito')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('PIX')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM payment_methods");
    }
}
```

---

### `investment_types`

```php
<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609110300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for investment_types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Renda Fixa')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Ações')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Cripto')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Tesouro Direto')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Caixinha')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Fundo Imobiliário')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM investment_types");
    }
}

```

---

Se quiser, posso também organizar todos os arquivos dessas migrations em `.zip` para facilitar a importação no seu projeto.

Deseja isso?
