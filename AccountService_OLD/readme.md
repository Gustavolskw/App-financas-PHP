## Aqui está a documentação completa e atualizada do `AuthService`

### Download do Repositório:

```bash
git clone git@github.com:Gustavolskw/Auth_service-php.git
```

---

### Ambiente:

**Ambiente de criacao do container automatizado:**

docker-compose.yml

```dockerfile
# Use PHP 8.4 image
FROM php:8.4-cli

# Install required extensions and tools
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev inotify-tools libssl-dev libcurl4-openssl-dev \
    && docker-php-ext-install zip sockets pdo pdo_mysql \
    && pecl install openswoole \
    && docker-php-ext-enable openswoole
# Install Composer (latest version)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /

# Copy composer files first (to install dependencies early)
COPY composer.json ./

# Install PHP dependencies
RUN composer install --no-dev --prefer-dist --no-interaction

# Run composer dump-autoload to optimize the autoloader
RUN composer dump-autoload --optimize

# Copy the rest of the application code
COPY . .

# Expose the port used by OpenSwoole server
EXPOSE 9502

# Default command to start the PHP server
CMD ["php", "server.php"]


```

**Após a criação dos cainter executar os mesmos:**

se for a primeira vez subindo o container da aplicacao, realizar as migracoes com o seguinte comando:

```bash
docker exec -it account-service php vendor/bin/doctrine-migrations migrations:migrate --all-or-nothing
```

apos isso somente responder com "yes"

```bash
> yes
```

---

### **Documentação Completa do AuthService**

#### **Objetivo**

O `AuthService` é um microserviço projetado para:

- Registrar usuários no banco de dados (`POST /register`) com validação robusta de entradas usando `illuminate/validation`.
- Buscar um usuário específico por email (`GET /user`).
- Buscar todos os usuários registrados (`GET /users`).
- Proteger rotas específicas por método e URI com autenticação JWT.
- Publicar eventos de registro no RabbitMQ para integração com outros serviços.
- Usar o Eloquent como ORM, DTOs para controle de dados retornados e uma estrutura modular com controllers, serviços e roteamento avançado.

#### **Estrutura do Projeto**

```
AuthService/
├── src/                    # Código fonte do projeto
│   ├── Config/            # Configurações gerais
│   │   └── Database.php   # Configuração do Eloquent
│   ├── Controllers/       # Controladores para processar requisições HTTP
│   │   └── AccController.php
│   ├── DTO/               # Data Transfer Objects para controle de dados
│   │   └── UserDTO.php
│   ├── Http/              # arquivos para consumo de APIs/outros Microservicos pro Http
│   │   └── ExternalConsumer.php
│   ├── Entity/            # Modelos do Eloquent (entidades do banco)
│   │   └── User.php
│   │   └── HttpResponse.php
│   ├── Message/           # Integração com mensageria
│   │   └── RabbitMQProducer.php
│   ├── Router/            # Definição de rotas
│   │   └── Routes.php
│   ├── Services/          # Lógica de negócios
│   │   └── AccService.php
├── migrations/            # Scripts de migração do banco
│   └── Version20250408215700.php
├── .env                   # Variáveis de ambiente
├── composer.json          # Dependências e autoload
├── migrations.php         # configuracao de ambiente do Doctrine Migrations
├── migrations_db.php      # configuracao de ambiente de conexcao ao banco de dados para o Doctrine Migrations
├── server.php             # Servidor Swoole
└── README.md              # Documentação básica (opcional)
```

---

### **Arquivos e Funcionalidades**

#### **1. `.env`**

- **Descrição**: Arquivo de configuração de variáveis de ambiente.
- **Funcionalidade**: Define parâmetros para conexão com banco de dados, RabbitMQ e JWT.
- **Conteúdo**:

  ```env
  DB_HOST=*****
  DB_NAME=***YOUR_DATABASE***
  DB_PORT=***SERVER_PORT***
  DB_USER=****
  DB_PASS=****
  DB_DRIVER=mysql
  RABBITMQ_HOST=RABBITMQ_SERVICE-HOST
  RABBITMQ_PORT=5672
  RABBITMQ_USER=guest
  RABBITMQ_PASS=guest
  RABBITMQ_VHOST=/
  RABBITMQ_CONSUME_QUEUE=caixa.criar-inicial
  RABBITMQ_FAN_OUT_EXCHANGE_INACT=auth.user.inactivated
  RABBITMQ_FAN_OUT_EXCHANGE_REACT=auth.user.reactivated

  JWT_SECRET=Gere com: openssl rand -base64 32
  ```

1. **Copiar o arquivo .env-example e formar o .env**:

```bash
cp .env.example .env
```

2. **Gerar uma chave segura para o Jwt**:

```bash
  openssl rand -base64 32
```

---

#### **2. `composer.json`**

- **Descrição**: Define dependências e configurações de autoload.
- **Funcionalidade**: Garante que todas as bibliotecas necessárias estejam disponíveis e mapeia namespaces.
- **Conteúdo**:

  ```json
  {
    "name": "account/service",
    "description": "Microservice to Manage the Users accounts",
    "type": "project",
    "autoload": {
      "psr-4": {
        "Acc\\": "src/",
        "Acc\\Migrations\\": "migrations/",
        "Acc\\Translations\\": "src/Translations/"
      }
    },
    "authors": [
      {
        "name": "Gustavolskw",
        "email": "gustavolschmidt13@gmail.com"
      }
    ],
    "require": {
      "php": ">=8.0",
      "php-amqplib/php-amqplib": "^3.5",
      "illuminate/database": "^10.0",
      "illuminate/validation": "^10.0",
      "openswoole/core": "22.1.5",
      "dompdf/dompdf": "^3.1",
      "vlucas/phpdotenv": "^5.6",
      "firebase/php-jwt": "^6.0",
      "guzzlehttp/guzzle": "^7.4",
      "doctrine/migrations": "^3.9",
      "symfony/yaml": "^7.2"
    },
    "minimum-stability": "stable",
    "scripts": {
      "start": "php server.php"
    }
  }
  ```

---

### **Comandos para Migrações**

#### **Gerar uma Nova Migração**

- **Comando**:
  ```bash
   php vendor/bin/doctrine-migrations migrations:generate
  ```
- **Resultado**: Gera um arquivo como `migrations/Version20250408215700.php`.
- **Ação**: Edite o arquivo gerado para definir a estrutura da tabela.

```php
<?php

declare(strict_types=1);

namespace Auth\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408215700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generating accounts table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `accounts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `userId` bigint NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
)");

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('accounts');

    }
}

```
