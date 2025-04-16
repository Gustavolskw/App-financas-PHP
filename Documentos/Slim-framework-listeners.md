```bash
composer create-project slim/slim-skeleton backend
```

dockerfile

```dockerfile
# Dockerfile
FROM php:8.2-apache

# Instala extensoes e ferramentas necessarias
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpq-dev \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# Copia arquivos
COPY . /var/www/html
COPY supervisord.conf /etc/supervisord.conf

# Permissões
RUN chown -R www-data:www-data /var/www/html

# Define entrypoint
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

```

Agora vamos adicionar os próximos arquivos:

---

### 📄 `supervisord.conf`

```ini
[supervisord]
nodaemon=true

[program:apache]
command=/usr/sbin/apache2ctl -D FOREGROUND
priority=10
autostart=true
autorestart=true

[program:consumer]
command=php /var/www/html/consume.php
priority=20
autostart=true
autorestart=true
```

---

### 📄 `consume.php`

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Acc\Message\CaixaUserCreationQueueConsumer;
use Acc\Message\CaixaUserInactivationExchangeConsumer;
use Acc\Message\CaixaUserReactivationExchangeConsumer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function fork(callable $callback) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('Erro ao criar processo');
    } elseif ($pid === 0) {
        $callback();
        exit(0);
    }
}

fork(function () {
    $consumer = new CaixaUserCreationQueueConsumer();
    $consumer->consumeFromQueue($_ENV['RABBITMQ_CONSUME_QUEUE']);
});

fork(function () {
    $consumer = new CaixaUserInactivationExchangeConsumer();
    $consumer->consumeFromExchange();
});

fork(function () {
    $consumer = new CaixaUserReactivationExchangeConsumer();
    $consumer->consumeFromExchange();
});

// Aguarda todos os filhos
while (pcntl_wait($status) != -1);
```

---

### 📄 `.env`

```env
RABBITMQ_CONSUME_QUEUE=caixa_user_creation
JWT_SECRET=seu_segredo_aqui
```

---

### 🛠️ Build e exec

```bash
docker-compose up --build
```

---

Tudo estará rodando no mesmo container:

- Apache servindo o Slim na porta `8080`
- Consumers ativos paralelamente com `pcntl_fork` controlados via `supervisord`.

Se quiser, posso gerar esse projeto como `.zip` também. Deseja isso?

----

----

Perfeito! Você está optando por um modelo que mantém **toda a lógica e execução no mesmo container**, com **múltiplos processos (threads via `pcntl_fork`)**, exatamente como você fazia com OpenSwoole. Isso é totalmente viável em PHP desde que você controle esses processos.

Vamos consolidar sua ideia e reforçar a arquitetura ideal para esse seu estilo 👇

---

## ✅ Modelo final escolhido: Tudo no mesmo container

| Componente                  | Tipo          | Executado como     | Processamento |
|----------------------------|---------------|--------------------|---------------|
| Slim API                   | Servidor HTTP | Apache ou NGINX    | Thread 1      |
| `consume.php`              | Daemon        | `pcntl_fork()`     | Threads 2..N  |
| Lógica de negócio          | Compartilhada | `/src/Services/...`| Usada por todos |

---

## 🧱 Exemplo de estrutura do projeto

```
/var/www/html/
├── public/
│   └── index.php                  <-- Slim inicia aqui
├── src/
│   ├── Routes/                    <-- Slim Routes
│   ├── Services/                  <-- Serviços reutilizados por API e Consumers
│   └── Message/
│       ├── CaixaUserCreationQueueConsumer.php
│       ├── CaixaUserInactivationExchangeConsumer.php
│       └── CaixaUserReactivationExchangeConsumer.php
├── consume.php                    <-- Cria 3 processos paralelos para escutar RabbitMQ
├── Dockerfile
├── supervisord.conf              <-- Roda Apache + consume.php juntos
└── .env
```

---

## ✅ Como funciona:

### 🔁 `consume.php`:

```php
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function fork($callback) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        die("Erro ao criar processo\n");
    } elseif ($pid === 0) {
        $callback();
        exit(0);
    }
}

// 3 escutas paralelas
fork(function () {
    (new CaixaUserCreationQueueConsumer())->consumeFromQueue($_ENV['RABBITMQ_CONSUME_QUEUE']);
});

fork(function () {
    (new CaixaUserInactivationExchangeConsumer())->consumeFromExchange();
});

fork(function () {
    (new CaixaUserReactivationExchangeConsumer())->consumeFromExchange();
});

// Aguarda os processos
while (pcntl_wait($status) !== -1);
```

---

## 🔧 Dockerfile com Apache + Supervisord:

```dockerfile
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia aplicação
COPY . /var/www/html/
COPY supervisord.conf /etc/supervisord.conf

RUN chown -R www-data:www-data /var/www/html

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

---

## 🔧 `supervisord.conf`:

```ini
[supervisord]
nodaemon=true

[program:apache]
command=/usr/sbin/apache2ctl -D FOREGROUND
autostart=true
autorestart=true
priority=10

[program:consumer]
command=php /var/www/html/consume.php
autostart=true
autorestart=true
priority=20
```

---

## 🧠 Benefícios do seu modelo:

| Vantagem | Por quê? |
|---------|----------|
| ✅ Toda lógica no mesmo container | Nada duplicado, 100% reaproveitável |
| ✅ Workers paralelos nativos | Com `pcntl_fork()` |
| ✅ API + Consumers juntos | Facilidade de deploy |
| ✅ Menor sobrecarga de DevOps | Um container só |

---

## 📦 Conclusão

Esse modelo é altamente eficaz para sua realidade atual, especialmente se:
- O volume de mensagens for moderado
- A aplicação estiver sendo executada em VPS, Docker local ou ambiente controlado
- Você quiser **evitar overhead de microservices separados**

Se quiser, posso empacotar esse projeto com tudo isso (estrutura, Dockerfile, supervisord, exemplo de consumer, etc.) e te mandar um `.zip` ou preparar um repo base. Deseja isso?