
# webociti_interns_2023_be

Prerequisite

- PHP 8.2 with below extension

    php8.2-common

    php8.2-dom

    php8.2-curl

    php8.2-mbstring
    
    php8.2-ctype

    php8.2-fileinfo

    php8.2-json

    php8.2-filter

    php8.2-hash

    php8.2-openssl

    php8.2-pcre

    php8.2-PDO

    php8.2-session

    php8.2-tokenizer

    php8.2-xml
    

    
- Composer 2

- MySQL

- Apache2


Setup the project in local run below command in sequence

    composer install

    php artisan key:generate

    php artisan jwt:secret

    php artisan migrate

    php artisan db:seed

    php artisan l5-swagger:generate

