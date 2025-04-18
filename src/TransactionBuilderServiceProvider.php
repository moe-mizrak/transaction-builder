<?php

declare(strict_types=1);

namespace MoeMizrak\TransactionBuilder;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use MoeMizrak\TransactionBuilder\Facades\TransactionBuilder;

/**
 * Service provider for TransactionBuilder
 *
 * @class TransactionBuilderServiceProvider
 */
final class TransactionBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void {}

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        /*
         * When Facade is called, it will return an instance of TransactionBuilder.
         */
        $this->app->bind('transaction-builder', function () {
            return new Transaction;
        });

        /*
         * Register the facade alias.
         */
        AliasLoader::getInstance()->alias('TransactionBuilder', TransactionBuilder::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return ['transaction-builder'];
    }
}
