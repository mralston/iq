# Changelog

## 1.1.0

### Added

- `iq.timezone` setting (`IQ_TIMEZONE`): every date the service stamps is taken in it. Null, the
  default, uses the application's timezone.
- `CustomerService::withNovaQuoteUuid()` writes `tCustomers.NovaQuoteUuid` and allows a customer
  without a `model_id` (its `tQuotes` row gets `QuoteId` 0). The column is never written when it
  is not set.
- `Customer::findByNovaQuoteUuid()`.
- `CustomerService::reset()`.
- Pest test suite on in-memory SQLite (`composer test`).

### Changed

- `create()` runs in a transaction on the `iq` connection, so a failure rolls back every IQ row it
  wrote.
- `LastOrderNo` and `LastCustInvoice` are read from `tUs` under an update lock inside that
  transaction, so concurrent sales never share an order or invoice number.
- Builder state is cleared after every `create()`, whether it succeeds or fails, and the
  `Customer` facade resolves a fresh service on each call.
- The visit's `VatRateId` is now written (it was listed but not fillable, so it was dropped).
- `User::branch()` and `Branch::users()` are public and use the `BranchId` and `Id` keys.

### Fixed

- The visit's `PanelTypeId` is read from the panel type's `Id` and is no longer always null.
- The service works under `Model::shouldBeStrict()`: the `branch` property is declared, the
  company counters are no longer saved through an `update()` call with a numeric key, and
  attributes read without a default fall back to null.
- `illuminate/database` constraint for Laravel 7 (`^7.0`).
