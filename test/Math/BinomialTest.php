<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Math;

use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Math\Binomial;
use ZxcvbnPhp\Math\BinomialProvider;

class BinomialTest extends TestCase
{
    public function binomialDataProvider()
    {
        return [
            [     0,    0,           1.0 ],
            [     1,    0,           1.0 ],
            [     5,    0,           1.0 ],
            [     0,    1,           0.0 ],
            [     0,    5,           0.0 ],
            [     2,    1,           2.0 ],
            [     4,    2,           6.0 ],
            [    33,    7,     4272048.0 ],
            [   206,  202,    72867865.0 ],
            [     3,    5,           0.0 ],
            [ 29847,    2,   445406781.0 ],
            [    49,   12, 92263734836.0 ],
        ];
    }

    public function testHasProvider()
    {
        $this->assertNotEmpty(Binomial::getUsableProviderClasses());
    }

    public function testChosenProviderMatchesExpected()
    {
        $providerClasses = Binomial::getUsableProviderClasses();

        $this->assertInstanceOf(reset($providerClasses), Binomial::getProvider());
    }

    /**
     * @dataProvider binomialDataProvider
     * @param int   $n
     * @param int   $k
     * @param float $expected
     */
    public function testBinomialCoefficient($n, $k, $expected)
    {
        foreach (Binomial::getUsableProviderClasses() as $providerClass) {
            $provider = new $providerClass();
            $this->assertInstanceOf(BinomialProvider::class, $provider);

            $value = $provider->binom($n, $k);
            $this->assertSame($expected, $value, "$providerClass returns expected result for ($n, $k)");

            if ($k <= $n) {  // Behavior is undefined for $k > n; don't test that
                $flippedValue = $provider->binom($n, $n - $k);
                $this->assertSame($value, $flippedValue, "$providerClass is symmetrical");
            }
        }
    }
}
