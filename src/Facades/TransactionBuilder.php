<?php

declare(strict_types=1);

namespace MoeMizrak\TransactionBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use MoeMizrak\TransactionBuilder\Transaction;

/**
 * Facade for TransactionBuilder.
 *
 * @method static Transaction make() - Creates a new instance of the Transaction class so that you can chain methods on it.
 *
 * @class TransactionBuilder
 */
final class TransactionBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'transaction-builder';
    }
}
