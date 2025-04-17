<?php

declare(strict_types=1);

namespace MoeMizrak\TransactionBuilder\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MoeMizrak\TransactionBuilder\Facades\TransactionBuilder;
use MoeMizrak\TransactionBuilder\TransactionBuilderServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            TransactionBuilderServiceProvider::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function getPackageAliases($app): array
    {
        return [
            'TransactionBuilder' => TransactionBuilder::class,
        ];
    }
}
