<?php

namespace ZxcvbnPhp\Matchers;

use ZxcvbnPhp\Scorer;

/**
 * Class Bruteforce
 * @package ZxcvbnPhp\Matchers
 *
 * Intentionally not named with Match suffix to prevent autoloading from Matcher.
 */
class Bruteforce extends BaseMatch
{

    public const BRUTEFORCE_CARDINALITY = 10;

    public $pattern = 'bruteforce';

    /**
     * @param string $password
     * @param array $userInputs
     * @return Bruteforce[]
     */
    public static function match(string $password, array $userInputs = []): array
    {
        // Matches entire string.
        $match = new static($password, 0, mb_strlen($password) - 1, $password);
        return [$match];
    }


    public function getFeedback(bool $isSoleMatch): array
    {
        return [
            'warning' => "",
            'suggestions' => [
            ]
        ];
    }

    public function getRawGuesses(): int
    {
        $guesses = pow(self::BRUTEFORCE_CARDINALITY, mb_strlen($this->token));
        if ($guesses > PHP_INT_MAX) {
            return PHP_INT_MAX;
        }

        // small detail: make bruteforce matches at minimum one guess bigger than smallest allowed
        // submatch guesses, such that non-bruteforce submatches over the same [i..j] take precedence.
        if (mb_strlen($this->token) === 1) {
            $minGuesses = Scorer::MIN_SUBMATCH_GUESSES_SINGLE_CHAR + 1;
        } else {
            $minGuesses = Scorer::MIN_SUBMATCH_GUESSES_MULTI_CHAR + 1;
        }

        return (int)max($guesses, $minGuesses);
    }
}
