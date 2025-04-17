# TransactionBuilder

A lightweight fluent wrapper around Laravel `DB::transaction()` with support for retries, on-failure callbacks, and result access.

## Installation

```bash
composer require moe-mizrak/transaction-builder
```

## Usage

```php
$result = TransactionBuilder::make()
    ->attempt(3) // number of attempts
    ->run(function () {
        // your transactional code here
        return 'done';
    })
    ->result();
```

### Nested Transactions

```php
$result = TransactionBuilder::make()
    ->run(function () {
        throw new \Exception("fail");
    })
    ->onFailure(function ($e) {
        logger()->error($e->getMessage());
    })
    ->disableThrow()
    ->result();
```

You can also do this:

```php
$result = TransactionBuilder::make()
    ->run(function () {
        // outer transaction logic
        DB::transaction(function () {
            // inner transaction logic
        });
    })
    ->result();

```

## Methods
- `attempt(int $attempts)`: The number of attempts to run the transaction. - default is 1.
- `run(callable $callback)`: The callback to be executed within the transaction.
- `onFailure(callable $callback)`: The callback to be executed if the transaction fails after all attempts.
- `disableThrow()`: Disable throwing exceptions on failure. - default is false (meaning that exceptions will be thrown as usual).
- `result()`: Get the result of the transaction. If the transaction fails, it will return null if `disableThrow()` is called, or throw an exception otherwise.