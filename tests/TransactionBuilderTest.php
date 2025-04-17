<?php

declare(strict_types=1);

namespace MoeMizrak\TransactionBuilder\Tests;

use Exception;
use Illuminate\Support\Facades\DB;
use Mockery;
use MoeMizrak\TransactionBuilder\Facades\TransactionBuilder;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class TransactionBuilderTest extends TestCase
{
    #[Test]
    public function testSuccessfulTransactionReturnsResult(): void
    {
        /* SETUP */
        $expected = 'test result';
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn($callback) => is_callable($callback)), 1)
            ->andReturn($expected);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()->run(fn() => $expected);

        /* ASSERT */
        $this->assertSame($expected, $transaction->result());
    }

    #[Test]
    public function testCustomAttemptsAreUsed(): void
    {
        /* SETUP */
        $expected = 42;
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'), 5)
            ->andReturn($expected);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()
            ->attempts(5)
            ->run(fn() => $expected);

        /* ASSERT */
        $this->assertSame($expected, $transaction->result());
    }

    #[Test]
    public function testExceptionInRunWillBeThrownByResult(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()->run(fn() => null);

        /* ASSERT */
        $this->expectExceptionObject($exception);
        $transaction->result();
    }

    #[Test]
    public function testDisableThrowSuppressesExceptionAndReturnsNull(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()
            ->disableThrow()
            ->run(fn() => null);

        /* ASSERT */
        $this->assertNull($transaction->result());
    }

    #[Test]
    public function testOnFailureCallbackIsInvokedWithExceptionWhenDisableThrow(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);
        $called = false;
        $caught = null;

        /* EXECUTE */
        TransactionBuilder::make()
            ->run(fn() => null)
            ->onFailure(function (Throwable $e) use (&$called, &$caught) {
                $called = true;
                $caught = $e;
            })
            ->disableThrow()
            ->result();

        /* ASSERT */
        $this->assertTrue($called);
        $this->assertSame($exception, $caught, 'test exception');
    }

    #[Test]
    public function testOnFailureCallbackIsInvokedWithException(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);
        $called = false;
        $caught = null;

        /* EXECUTE */
        TransactionBuilder::make()
            ->run(fn() => null)
            ->onFailure(function (Throwable $e) use (&$called, &$caught) {
                $called = true;
                $caught = $e;
            });

        /* ASSERT */
        $this->assertTrue($called);
        $this->assertSame($exception, $caught, 'test exception');
    }

    #[Test]
    public function testOnFailureNotCalledWhenNoException(): void
    {
        /* SETUP */
        $expected = 'test result';
        DB::shouldReceive('transaction')
            ->once()
            ->andReturn($expected);
        $called = false;

        /* EXECUTE */
        TransactionBuilder::make()
            ->run(fn() => $expected)
            ->onFailure(fn() => $called = true)
            ->result();

        /* ASSERT */
        $this->assertFalse($called);
    }

    #[Test]
    public function testRunReturnsNullIfCallbackReturnsNull(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->once()
            ->andReturn(null);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()->run(function () {});

        /* ASSERT */
        $this->assertNull($transaction->result());
    }

    #[Test]
    public function testDisableThrowAfterRunSuppressesException(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::make()
            ->run(fn() => null)
            ->disableThrow();

        /* ASSERT */
        $this->assertNull($transaction->result());
    }

    #[Test]
    public function testMethodsAreChainable(): void
    {
        /* SETUP */
        $transaction = TransactionBuilder::make();

        /* EXECUTE & ASSERT */
        $this->assertSame($transaction, $transaction->attempts(2));
        $this->assertSame($transaction, $transaction->disableThrow());
        $this->assertSame($transaction, $transaction->run(fn() => 'x'));
        $this->assertSame($transaction, $transaction->onFailure(fn() => null));
    }

    #[Test]
    public function testNestedTransactionIsHandledProperly(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn($callback) => is_callable($callback)), 1)
            ->andReturnUsing(function ($outerCallback) {
                return $outerCallback();
            });
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn($callback) => is_callable($callback)), 1)
            ->andReturn('nested');

        /* EXECUTE */
        $transaction = TransactionBuilder::make()->run(function () {
            return TransactionBuilder::make()->run(fn() => 'nested')->result();
        });

        /* ASSERT */
        $this->assertSame('nested', $transaction->result());
    }

    #[Test]
    public function testExceptionInNestedTransactionIsHandled(): void
    {
        /* SETUP */
        $ex = new Exception('nested boom');
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($outerCallback) {
                return $outerCallback();
            });
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($ex);
        $caught = null;

        /* EXECUTE */
        $transaction = TransactionBuilder::make()->run(function () use (&$caught) {
            return TransactionBuilder::make()
                ->run(fn() => null)
                ->onFailure(function (Throwable $e) use (&$caught) {
                    $caught = $e;
                })
                ->disableThrow()
                ->result();
        });

        /* ASSERT */
        $this->assertSame($ex, $caught);
        $this->assertNull($transaction->result());
    }

    #[Test]
    public function testMultipleTransactionsCanBeRun(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->twice()
            ->andReturn('first', 'second');
        $transaction = TransactionBuilder::make();

        /* EXECUTE */
        $first = $transaction->run(fn() => 'first')->result();
        $second = $transaction->run(fn() => 'second')->result();

        /* ASSERT */
        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
