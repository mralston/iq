# IQ Solar

## Introduction

This package provides a way to work with the IQ Solar database.

## Config

This package uses the `iq` database connection. Add the following to `config/database.php`:

```php
'iq' => [
    'driver' => 'sqlsrv',
    'url' => env('IQ_DATABASE_URL'),
    'host' => env('IQ_DB_HOST'),
    'port' => env('IQ_DB_PORT', 1433),
    'database' => env('IQ_DB_DATABASE'),
    'username' => env('IQ_DB_USERNAME'),
    'password' => env('IQ_DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
],
```

The database connection can then be set up as follows in the .env file:

```dotenv
IQ_DB_HOST=<IQ SQL Server hostname or IP address>
IQ_DB_DATABASE=<IQ SQL Server database>
IQ_DB_USERNAME=<IQ SQL Server username>
IQ_DB_PASSWORD=<IQ SQL Server password>
```
## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

PDF is open-sourced software licenced under the [MIT license](LICENSE.md).

IQ Solar is produced by and remains copyright of [Lewis John Limited](https://www.lewis-john.co.uk/).  