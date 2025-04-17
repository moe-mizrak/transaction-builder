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
     */
    public function boot(): void
    {
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configure();

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
     */
    public function provides(): array
    {
        return ['transaction-builder'];
    }

    /**
     * Setup the configuration.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/transaction-builder.php', 'transaction-builder'
        );
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/transaction-builder.php' => config_path('transaction-builder.php'),
            ], 'transaction-builder');
        }
    }
}
