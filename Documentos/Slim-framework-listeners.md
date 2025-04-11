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