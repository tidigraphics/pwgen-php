<?php

namespace PWGen\Generator;

use PWGen\PasswordElement;

/**
 * Class PhonemesPasswordGenerator
 *
 * @package PWGen\Generator
 */
class PhonemesPasswordGenerator extends AbstractGenerator
{
    /**
     * @var PasswordElement[]
     */
    private $elements;

    /**
     * @var array
     */
    private $temporaryPassword;

    /**
     * PhonemesPasswordGenerator constructor.
     *
     * @param int $flags
     * @param int $length
     */
    public function __construct($flags, $length)
    {
        $this->flags  = $flags;
        $this->length = $length;

        $this->initPhonemes();
    }

    /**
     * @param int $flags
     * @param int $length
     *
     * @return PhonemesPasswordGenerator
     */
    public static function create($flags, $length)
    {
        return new self($flags, $length);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {

        $this->temporaryPassword = array();

        do {
            $generatorFlags = $this->flags;
            $c              = 0;
            $prev           = 0;
            $shouldBe       = self::randomInt(0, 1) ? self::FLAG_VOWEL : self::FLAG_CONSONANT;
            $first          = 1;

            while ($c < $this->length) {
                $i     = self::randomInt(0, count($this->elements) - 1);
                $str   = $this->elements[$i]->getString();
                $len   = strlen($str);
                $flags = $this->elements[$i]->getFlags();

                // Filter on the basic type of the next element
                if (($flags & $shouldBe) == 0) {
                    continue;
                }
                // Handle the NOT_FIRST flag
                if ($first && ($flags & self::FLAG_NOT_FIRST)) {
                    continue;
                }
                // Don't allow VOWEL followed a Vowel/Dipthong pair
                if (($prev & self::FLAG_VOWEL) && ($flags & self::FLAG_VOWEL) &&
                    ($flags & self::FLAG_DIPHTHONG)
                ) {
                    continue;
                }

                // Handle the AMBIGUOUS flag
                if ($flags & self::FLAG_AMBIGUOUS) {
                    if (strpbrk($str, $this->ambiguous) !== false) {
                        continue;
                    }
                }

                // Don't allow us to overflow the buffer
                if ($len > $this->length - $c) {
                    continue;
                }

                /*
                 * OK, we found an element which matches our criteria,
                 * let's do it!
                 */
                for ($j = 0; $j < $len; $j++) {
                    $this->temporaryPassword[$c + $j] = $str[$j];
                }

                // Handle PW_UPPERS
                if ($this->flags & self::FLAG_UPPERS) {
                    if (($first || $flags & self::FLAG_CONSONANT)
                        && (self::randomInt(0, 9) < 2)
                        && (
                            ($flags & self::FLAG_AMBIGUOUS)
                            || strpos($this->ambiguous, strtoupper($this->temporaryPassword[$c])) === false
                        )
                    ) {
                        $this->temporaryPassword[$c] = strtoupper($this->temporaryPassword[$c]);
                        $generatorFlags              &= ~self::FLAG_UPPERS;
                    }
                }

                // Handle the AMBIGUOUS flag
                if ($this->flags & self::FLAG_AMBIGUOUS) {
                    if (strpbrk(implode('', $this->temporaryPassword), $this->ambiguous) !== false) {
                        continue;
                    }
                }

                $c += $len;

                // Time to stop?
                if ($c >= $this->length) {
                    break;
                }

                // Handle PW_DIGITS
                if ($this->flags & self::FLAG_DIGITS) {
                    if (!$first && (random_int(0, 9) < 3)) {
                        do {
                            $ch = strval(random_int(0, 9));
                        } while (($this->flags & self::FLAG_AMBIGUOUS) &&
                        strpos($this->ambiguous, $ch) !== false);
                        $this->temporaryPassword[$c++] = $ch;
                        $generatorFlags                &= ~self::FLAG_DIGITS;

                        $first = 1;
                        $prev  = 0;

                        $shouldBe = random_int(0, 1) ? self::FLAG_VOWEL : self::FLAG_CONSONANT;
                        continue;
                    }
                }

                // Handle PW_SYMBOLS
                if ($this->flags & self::FLAG_SYMBOLS) {
                    if (!$first && (random_int(0, 9) < 2)) {
                        do {
                            $ch = $this->symbols[random_int(
                                0,
                                strlen($this->symbols) - 1
                            )];
                        } while (($this->flags & self::FLAG_AMBIGUOUS) &&
                        strpos($this->ambiguous, $ch) !== false);
                        $this->temporaryPassword[$c++] = $ch;
                        $generatorFlags                &= ~self::FLAG_SYMBOLS;
                    }
                }

                // OK, figure out what the next element should be
                if ($shouldBe == self::FLAG_CONSONANT) {
                    $shouldBe = self::FLAG_VOWEL;
                } else { // should_be == VOWEL
                    if (($prev & self::FLAG_VOWEL) || ($flags & self::FLAG_DIPHTHONG) ||
                        (random_int(0, 9) > 3)
                    ) {
                        $shouldBe = self::FLAG_CONSONANT;
                    } else {
                        $shouldBe = self::FLAG_VOWEL;
                    }
                }
                $prev  = $flags;
                $first = 0;
            }
        } while ($generatorFlags & (self::FLAG_UPPERS | self::FLAG_DIGITS | self::FLAG_SYMBOLS));

        return implode('', $this->temporaryPassword);
    }

    /**
     * Generate a random number n, where $min <= n < $max
     * The prefered order of RNGs is:
     *  - php7's random_int
     *  - OpenSSL's openssl_random_pseudo_bytes
     *  - mcrypt's mcrypt_create_iv (deprecated in PHP 7.1.0)
     *  - php's mt_rand (not actually cryptographically secure at all)
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    public static function randomInt($min = 0, $max = 0)
    {
        if ($min > $max) {
            return false;
        }

        if (function_exists('random_int')) {
            return random_int($min, $max);
        } else {
            // mcrypt was deprecated in PHP 7.1.0, prefer OpenSSL
            $use_openssl = function_exists('openssl_random_pseudo_bytes');
            $use_mcrypt  = function_exists('mcrypt_create_iv');
            if ($use_openssl || $use_mcrypt) {
                $rnd = unpack('L', $use_openssl
                    ? openssl_random_pseudo_bytes(4)
                    : mcrypt_create_iv(4, MCRYPT_DEV_URANDOM));
                // Because you can't unpack an unsigned long on a 32bit system (or rather, you can,
                // but it won't be unsigned), we need to clear the sign bit. mt_getrandmax() seems to
                // be 2147483647 (0x7FFFFFFF) on all platforms I've tested, so this doesn't change the
                // supported range.
                $rnd = $rnd[1] & 0x7FFFFFFF;
                return $rnd % (1 + $max - $min) + $min;
            } else {
                // fall back on cryptographically insecure rng
                return mt_rand($min, $max);
            }
        }
    }

    /**
     * This method initializes all elements needed to generate a password
     */
    private function initPhonemes()
    {
        $this->elements = [
            new PasswordElement('a', self::FLAG_VOWEL),
            new PasswordElement('ae', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('ah', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('ai', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('b', self::FLAG_CONSONANT),
            new PasswordElement('c', self::FLAG_CONSONANT),
            new PasswordElement('ch', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG),
            new PasswordElement('d', self::FLAG_CONSONANT),
            new PasswordElement('e', self::FLAG_VOWEL),
            new PasswordElement('ee', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('ei', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('f', self::FLAG_CONSONANT),
            new PasswordElement('g', self::FLAG_CONSONANT),
            new PasswordElement('gh', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG | self::FLAG_NOT_FIRST),
            new PasswordElement('h', self::FLAG_CONSONANT),
            new PasswordElement('i', self::FLAG_VOWEL),
            new PasswordElement('ie', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('j', self::FLAG_CONSONANT),
            new PasswordElement('k', self::FLAG_CONSONANT),
            new PasswordElement('l', self::FLAG_CONSONANT),
            new PasswordElement('m', self::FLAG_CONSONANT),
            new PasswordElement('n', self::FLAG_CONSONANT),
            new PasswordElement('ng', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG | self::FLAG_NOT_FIRST),
            new PasswordElement('o', self::FLAG_VOWEL),
            new PasswordElement('oh', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('oo', self::FLAG_VOWEL | self::FLAG_DIPHTHONG),
            new PasswordElement('p', self::FLAG_CONSONANT),
            new PasswordElement('ph', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG),
            new PasswordElement('qu', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG),
            new PasswordElement('r', self::FLAG_CONSONANT),
            new PasswordElement('s', self::FLAG_CONSONANT),
            new PasswordElement('sh', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG),
            new PasswordElement('t', self::FLAG_CONSONANT),
            new PasswordElement('th', self::FLAG_CONSONANT | self::FLAG_DIPHTHONG),
            new PasswordElement('u', self::FLAG_VOWEL),
            new PasswordElement('v', self::FLAG_CONSONANT),
            new PasswordElement('w', self::FLAG_CONSONANT),
            new PasswordElement('x', self::FLAG_CONSONANT),
            new PasswordElement('y', self::FLAG_CONSONANT),
            new PasswordElement('z', self::FLAG_CONSONANT)
        ];
    }
}
