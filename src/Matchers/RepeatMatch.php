<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

use JetBrains\PhpStorm\ArrayShape;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Scorer;

class RepeatMatch extends BaseMatch
{
    const GREEDY_MATCH = '/(.+)\1+/u';
    const LAZY_MATCH = '/(.+?)\1+/u';
    const ANCHORED_LAZY_MATCH = '/^(.+?)\1+$/u';

    public $pattern = 'repeat';

    /** @var MatchInterface[] An array of matches for the repeated section itself. */
    public $baseMatches = [];

    /** @var int The number of guesses required for the repeated section itself. */
    public $baseGuesses;

    /** @var int The number of times the repeated section is repeated. */
    public $repeatCount;

    /** @var string The string that was repeated in the token. */
    public $repeatedChar;

    /**
     * Match 3 or more repeated characters.
     *
     * @param string $password
     * @param array $userInputs
     * @return RepeatMatch[]
     */
    public static function match($password, $userInputs = [])
    {
        $matches = [];
        $lastIndex = 0;

        while ($lastIndex < mb_strlen($password)) {
            $greedyMatches = self::findAll($password, self::GREEDY_MATCH, $lastIndex);
            $lazyMatches = self::findAll($password, self::LAZY_MATCH, $lastIndex);

            if (empty($greedyMatches)) {
                break;
            }

            if (mb_strlen($greedyMatches[0][0]['token']) > mb_strlen($lazyMatches[0][0]['token'])) {
                $match = $greedyMatches[0];
                preg_match(self::ANCHORED_LAZY_MATCH, $match[0]['token'], $anchoredMatch);
                $repeatedChar = $anchoredMatch[1];
            } else {
                $match = $lazyMatches[0];
                $repeatedChar = $match[1]['token'];
            }

            $scorer = new Scorer();
            $matcher = new Matcher();

            $baseAnalysis = $scorer->getMostGuessableMatchSequence($repeatedChar, $matcher->getMatches($repeatedChar));
            $baseMatches = $baseAnalysis['sequence'];
            $baseGuesses = $baseAnalysis['guesses'];

            $repeatCount = mb_strlen($match[0]['token']) / mb_strlen($repeatedChar);

            $matches[] = new static(
                $password,
                $match[0]['begin'],
                $match[0]['end'],
                $match[0]['token'],
                [
                    'repeated_char' => $repeatedChar,
                    'base_guesses'  => $baseGuesses,
                    'base_matches'  => $baseMatches,
                    'repeat_count'  => $repeatCount,
                ]
            );

            $lastIndex = $match[0]['end'] + 1;
        }

        return $matches;
    }

    #[ArrayShape(['warning' => 'string', 'suggestions' => 'string[]'])]
    public function getFeedback($isSoleMatch)
    {
        $warning = mb_strlen($this->repeatedChar) == 1
            ? 'Repeats like "aaa" are easy to guess'
            : 'Repeats like "abcabcabc" are only slightly harder to guess than "abc"';

        return [
            'warning'     => $warning,
            'suggestions' => [
                'Avoid repeated words and characters',
            ],
        ];
    }

    /**
     * @param string $password
     * @param int $begin
     * @param int $end
     * @param string $token
     * @param array $params An array with keys: [repeated_char, base_guesses, base_matches, repeat_count].
     */
    public function __construct($password, $begin, $end, $token, $params = [])
    {
        parent::__construct($password, $begin, $end, $token);
        if (!empty($params)) {
            $repeatedChar = '';
            if(!empty($params['repeated_char'])) $repeatedChar = $params['repeated_char'];
            $this->repeatedChar = $repeatedChar;

            $baseGuesses = 0;
            if(!empty($params['base_guesses'])) $baseGuesses = $params['base_guesses'];
            $this->baseGuesses = $baseGuesses;

            $baseMatches = [];
            if(!empty($params['base_matches'])) $baseMatches = $params['base_matches'];
            $this->baseMatches = $baseMatches;

            $repeatCount = 0;
            if(!empty($params['repeat_count'])) $repeatCount = $params['repeat_count'];
            $this->repeatCount = $repeatCount;
        }
    }

    protected function getRawGuesses()
    {
        return $this->baseGuesses * $this->repeatCount;
    }
}
