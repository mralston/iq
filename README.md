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
## Timezone

IQ stores local wall-clock times. Every date the package stamps on a new customer (initial date,
visit, invoice, install note, process action due dates and the notes heading) is taken in
`iq.timezone`. Leave it null to use the application's timezone; set it when the application runs
in UTC but IQ expects UK time:

```dotenv
IQ_TIMEZONE=Europe/London
```

## Nova

A customer sold from a Nova quote is linked to it by `tCustomers.NovaQuoteUuid`
(`nvarchar(36)`), which must be added to IQ before it is used:

```php
$customer = Customer::withCompany($company)
    ->withRep($rep)
    // ...the other with* builders
    ->withNovaQuoteUuid($quote->uuid)
    ->withAttrs([...])
    ->create();

$customer = \Mralston\Iq\Models\Customer::findByNovaQuoteUuid($quote->uuid);
```

- Without `withNovaQuoteUuid()` the column is never written, so IQ databases without it keep
  working.
- With it, `model_id` may be left out: the customer's `ImportId` and `QuoteId` stay null and the
  `tQuotes` row is written with `QuoteId` 0. Without it, a missing `model_id` still throws
  `NoQuoteException`.
- `create()` writes everything in one transaction on the `iq` connection and takes the order and
  invoice numbers from `tUs` under an update lock, so it is safe to run from queued jobs at once.
- The service clears its builder state after every `create()`, and the facade resolves a fresh
  service for each call, so nothing set for one customer reaches the next.
- It runs under `Model::shouldBeStrict()`. To pass the rep's branch, eager load it
  (`$rep->load('branch')`) rather than reading `$rep->branch` lazily.

## Testing

```sh
composer install
composer test
```

The suite runs against in-memory SQLite registered as the `iq` connection.

## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

Package is open-sourced software licenced under the [MIT license](LICENSE.md).

IQ Solar is produced by and remains copyright of [Lewis John Limited](https://www.lewis-john.co.uk/).  
