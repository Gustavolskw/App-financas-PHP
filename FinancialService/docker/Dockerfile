FROM dunglas/frankenphp

WORKDIR /app

# Instalações necessárias e extensões PHP
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libicu-dev \
        libssl-dev \
        libbrotli-dev \
        zip \
        unzip \
        git \
        curl \
        libpq-dev \
        supervisor && \
    docker-php-ext-install \
        zip \
        sockets \
        pdo \
        pdo_mysql \
        bcmath \
        intl && \
   pecl install xdebug swoole mongodb && \
   docker-php-ext-enable xdebug swoole mongodb && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia o código da aplicação e configs
COPY ../ /app
COPY 90-xdebug.ini /usr/local/etc/php/conf.d/
COPY Caddyfile /etc/caddy/Caddyfile
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /app

RUN mkdir -p /app/logs \
 && chown -R www-data:www-data /app/logs \
 && chmod -R 775 /app/logs
# Permite execução do entrypoint
RUN chmod +x /usr/local/bin/entrypoint.sh

# Define o entrypoint padrão
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


# Comando para rodar o servidor FrankenPHP via Caddy
#CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]

