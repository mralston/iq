# IQ Solar

## Introduction

This package provides a way to work with the IQ Solar database.

## Config

This package uses the `sqlsrv` database connection. As both the `default` database connection and the `sqlsrv` will likely be used together, the environment variable names should be modified to make them distinct in `config/database.php`:

```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'url' => env('MSSQL_DATABASE_URL'),
    'host' => env('MSSQL_DB_HOST'),
    'port' => env('MSSQL_DB_PORT', 1433),
    'database' => env('MSSQL_DB_DATABASE'),
    'username' => env('MSSQL_DB_USERNAME'),
    'password' => env('MSSQL_DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
],
```

The database connection can then be set up as follows in the .env file:

```dotenv
MSSQL_DB_HOST=<IQ SQL Server hostname or IP address>
MSSQL_DB_DATABASE=<IQ SQL Server database>
MSSQL_DB_USERNAME=<IQ SQL Server username>
MSSQL_DB_PASSWORD=<IQ SQL Server password>
```
## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

PDF is open-sourced software licenced under the [MIT license](LICENSE.md).

IQ Solar is produced by and remains copyright of [Lewis John Limited](https://www.lewis-john.co.uk/).  