<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Impl;

abstract class AbstractBinomialProviderWithFallback extends AbstractBinomialProvider
{
    /**
     * @var AbstractBinomialProvider|null
     */
    private $fallback = null;

    protected function calculate($n, $k)
    {
        $calculate = $this->tryCalculate($n, $k);
        if(is_null($calculate)) $calculate = $this->getFallbackProvider()->calculate($n, $k);

        return $calculate;
    }

    abstract protected function tryCalculate($n, $k);

    abstract protected function initFallbackProvider();

    protected function getFallbackProvider()
    {
        if ($this->fallback === null) {
            $this->fallback = $this->initFallbackProvider();
        }

        return $this->fallback;
    }
}