## Clone Project

```sh
git clone https://github.com/DoanKhiem/WebLaravelApi
```

## Installation

### Required
- php version >= 8.2
- composer
- mysql

```sh
composer install
```

```sh
npm install
```

```sh
cp .env.example .env
```

- config database in .env file
```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mydatabase
DB_USERNAME=root
DB_PASSWORD=
```

```sh
php artisan key:generate
```

```sh
php artisan migrate
```

### Create folder pdf in folder storage/public/pdf

```sh
php artisan storage:link
```

### Compile and Hot-Reload for Development

```sh
php artisan serve
```
