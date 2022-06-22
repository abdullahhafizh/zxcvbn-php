<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Impl;

class BinomialProviderPhp73Gmp extends AbstractBinomialProvider
{
    /**
     * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
     * @noinspection PhpComposerExtensionStubsInspection
     */
    protected function calculate($n, $k)
    {
        return (float)gmp_strval(gmp_binomial($n, $k));
    }
}