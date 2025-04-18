<?php

declare(strict_types=1);

namespace MoeMizrak\TransactionBuilder;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Transaction class to handle database transactions with retry attempts, on-failure callbacks, and result retrieval.
 *
 * @class Transaction
 */
final class Transaction
{
    protected int $attempts = 1;

    protected bool $shouldThrow = true;

    protected ?Closure $callback = null;

    protected ?Closure $onFailure = null;

    protected mixed $result = null;

    protected ?Throwable $exception = null;

    /**
     * Create a new instance of the Transaction class.
     *
     * @return self
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Set the number of attempts for the transaction and return the instance.
     *
     * @param int $attempts
     *
     * @return $this
     */
    public function attempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Set the callback to be executed within the transaction.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function run(Closure $callback): self
    {
        $this->callback = $callback;

        // Execute the callback within a transaction and in case of failure, save the exception.
        try {
            $this->result = DB::transaction($callback, $this->attempts);
        } catch (Throwable $e) {
            $this->exception = $e;
        }

        return $this;
    }

    /**
     * Set the callback to be executed on failure (If we caught an exception in the run method).
     *
     * @param Closure $onFailure
     *
     * @return $this
     */
    public function onFailure(Closure $onFailure): self
    {
        $this->onFailure = $onFailure;

        // The caught exception is passed to the onFailure callback as function($e) { ... } so that you can handle/use it.
        if ($this->exception) {
            $onFailure($this->exception);
        }

        return $this;
    }

    /**
     * Set the shouldThrow flag to false, preventing the exception from being thrown.
     *
     * @return $this
     */
    public function disableThrow(): self
    {
        $this->shouldThrow = false;

        return $this;
    }

    /**
     * Get the result of the transaction or throw the exception if any exception was occurred.
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function result(): mixed
    {
        // If an exception was caught and the shouldThrow flag is set to true, throw the exception.
        if ($this->exception && $this->shouldThrow) {
            throw $this->exception;
        }

        return $this->result;
    }
}
