<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

require_once __DIR__ . '/../Commission.php';

class CommissionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetCurrency(): void
    {
        $commission = new Commission(45717360, 100.00, 'EUR');
        $this->assertEquals('EUR', $commission->getCurrency());
    }

    public function testCalculateAmountForEuCountry(): void
    {
        $commission = new Commission(45717360, 100.00, 'EUR');
        $commission->setIsEu(true);
        $result = $commission->calculateAmount(1.0);
        $this->assertEquals(1.0, $result);
    }

    public function testCalculateAmountForNonEuCountry(): void
    {
        $commission = new Commission(516793, 50.00, 'USD');
        $commission->setIsEu(false);
        $result = $commission->calculateAmount(1.0);
        $this->assertEquals(1.0, $result);
    }

    public function testCalculateAmountWithNonEUCurrency(): void
    {
        $commission = new Commission(516793, 50.00, 'USD');
        $commission->setIsEu(false);
        $result = $commission->calculateAmount(0.5);
        $this->assertEquals(2.0, $result);
    }

    public function testOutputResult(): void
    {
        $commission = new Commission(45717360, 100.00, 'EUR');
        $commission->setIsEu(true);
        $commission->calculateAmount(1.0);

        $this->expectOutputString("1\n");
        $commission->outputResult();
    }

    public function testEuCheckWithEuCountry(): void
    {
        $mock = Mockery::mock('Commission[euCheck]', [45717360, 100.00, 'EUR'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('euCheck')
            ->once()
            ->andReturn(true);

        $this->assertTrue($mock->euCheck());
    }

    public function testEuCheckWithNonEuCountry(): void
    {
        $mock = Mockery::mock('Commission[euCheck]', [516793, 50.00, 'USD'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('euCheck')
            ->once()
            ->andReturn(false);

        $this->assertFalse($mock->euCheck());
    }
}
