# TransactionBuilder

<a href="https://reboosty-reboosty.vercel.app/api?repo_url=https://github.com/moe-mizrak/transaction-builder" target="_blank">
  <img src="https://reboosty-reboosty.vercel.app/api?repo_url=https://github.com/moe-mizrak/transaction-builder" alt="reboosty" />
</a>

A lightweight fluent wrapper around Laravel `DB::transaction()` with support for retries, on-failure callbacks, and result access.

This package was created after [onFailureCallback PR](https://github.com/laravel/framework/pull/55338) which is rejected since it has a breaking change, so I've created `transaction-builder` package which provides `onFailureCallback` feature and even more can be added. 

## Installation

```bash
composer require moe-mizrak/transaction-builder
```

## Methods
- `make()`: Create a new instance of the TransactionBuilder.
- `attempts(int $attempts)`: The number of attempts to run the transaction. - default is 1.
- `run(callable $callback)`: The callback to be executed within the transaction.
- `onFailure(callable $callback)`: The callback to be executed if the transaction fails after all attempts.
- `disableThrow()`: Disable throwing exceptions on failure. - default is false (meaning that exceptions will be thrown as usual).
- `result()`: Get the result of the transaction. If the transaction fails, it will return null if `disableThrow()` is called, or throw an exception otherwise.

## Usage

```php
$result = TransactionBuilder::make()
    ->attempts(3) // number of attempts
    ->run(function () {
        // your transaction logic
        return 'done';
    })
    ->result();
```

### On Failure Callback

```php
$result = TransactionBuilder::make()
    ->run(function () {
        throw new \Exception("fail");
    })
    ->onFailure(function ($exception) {
        logger()->error($exception->getMessage());
    })
    ->disableThrow() // optional if you want to disable throwing exceptions since you already have onFailure callback
    ->result();
```

### Nested Transactions

```php
$result = TransactionBuilder::make()
    ->run(function () {
        // outer transaction logic
        
        TransactionBuilder::make()
            ->attempts(2) // number of attempts
            ->run(function () {
                // inner transaction logic
            })
            ->onFailure(function ($exception) {
                logger()->error($exception->getMessage());
            })
            ->result();
    })
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

## 💫 Contributing

> **Your contributions are welcome!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.

> If you find this package useful, please consider ⭐ it to show your support!

## 📜 License
Transaction Builder for Laravel is an open-sourced software licensed under the **[MIT license](LICENSE)**.
