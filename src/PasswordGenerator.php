<?php
namespace PWGen;

use PWGen\Generator\GeneratorInterface;
use PWGen\Generator\PhonemesPasswordGenerator;
use PWGen\Generator\SecurePasswordGenerator;

/**
 * Port of the famous GNU/Linux Password Generator ("pwgen") to PHP.
 * This file may be distributed under the terms of the GNU Public License.
 * Copyright (C) 2001, 2002 by Theodore Ts'o <tytso@alum.mit.edu>
 * Copyright (C) 2009 by Superwayne <superwayne@superwayne.org>
 */
class PasswordGenerator
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var array
     */
    private $temporaryPassword;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $length;

    /**
     * @var PasswordElement[]
     */
    private $elements;


    /**
     * @param int  $length     Length of the generated password. Default: 8
     * @param bool $secure     Generate completely random, hard-to-memorize passwords. These should only
     *                         be used for machine passwords, since otherwise it's almost guaranteed that
     *                         users will simply write the password on a piece of paper taped to the monitor...
     * @param bool $numerals   Include at least one number in the password. This is the default.
     * @param bool $capitalize Include at least one capital letter in the password. This is the default.
     * @param bool $ambiguous  Don't use characters that could be confused by the user when printed,
     *                         such as 'l' and '1', or '0' or 'O'. This reduces the number of possible
     *                         passwords significantly, and as such reduces the quality of the passwords.
     *                         It may be useful for users who have bad vision, but in general use of this
     *                         option is not recommended.
     * @param bool $noVowels   Generate random passwords that do not contain vowels or numbers that might be
     *                         mistaken for vowels. It provides less secure passwords to allow system
     *                         administrators to not have to worry with random passwords accidentally contain
     *                         offensive substrings.
     * @param bool $symbols    Include at least one special character in the password.
     */
    public function __construct(
        $length = 8,
        $secure = false,
        $numerals = true,
        $capitalize = true,
        $ambiguous = false,
        $noVowels = false,
        $symbols = false
    ) {
        $this->method = 'phonemes';

        $this->setLength($length);
        $this->setSecure($secure);
        $this->setNumerals($numerals);
        $this->setCapitalize($capitalize);
        $this->setAmbiguous($ambiguous);
        $this->setNoVowels($noVowels);
        $this->setSymbols($symbols);
    }

    /**
     * @param int        $length     Length of the generated password. Default: 8
     * @param bool|false $secure     Generate completely random, hard-to-memorize passwords. These should only
     *                               be used for machine passwords, since otherwise it's almost guaranteed that
     *                               users will simply write the password on a piece of paper taped to the monitor...
     * @param bool|true  $numerals   Include at least one number in the password. This is the default.
     * @param bool|true  $capitalize Include at least one capital letter in the password. This is the default.
     * @param bool|false $ambiguous  Don't use characters that could be confused by the user when printed,
     *                               such as 'l' and '1', or '0' or 'O'. This reduces the number of possible
     *                               passwords significantly, and as such reduces the quality of the passwords.
     *                               It may be useful for users who have bad vision, but in general use of this
     *                               option is not recommended.
     * @param bool|false $noVowels   Generate random passwords that do not contain vowels or numbers that might be
     *                               mistaken for vowels. It provides less secure passwords to allow system
     *                               administrators to not have to worry with random passwords accidentally contain
     *                               offensive substrings.
     * @param bool|false $symbols    Include at least one special character in the password.
     *
     * @return PasswordGenerator
     */
    public static function create(
        $length = 8,
        $secure = false,
        $numerals = true,
        $capitalize = true,
        $ambiguous = false,
        $noVowels = false,
        $symbols = false
    ) {
        return new self($length,
            $secure,
            $numerals,
            $capitalize,
            $ambiguous,
            $noVowels,
            $symbols);
    }

    /**
     * Length of the generated password. Default: 8
     *
     * @param int $length
     *
     * @return $this
     */
    public function setLength($length)
    {
        if (is_numeric($length) && $length > 0) {
            $this->length = $length;
            if ($this->length < 5) {
                $this->method = 'random';
            }
            if ($this->length <= 2) {
                $this->setCapitalize(false);
            }
            if ($this->length <= 1) {
                $this->setNumerals(false);
            }
        } else {
            $this->length = 8;
        }

        return $this;
    }

    /**
     * Generate completely random, hard-to-memorize passwords. These should only used for machine passwords,
     * since otherwise it's almost guaranteed that users will simply write the password on a piece of paper
     * taped to the monitor...
     * Please note that this function implies that you want passwords which include symbols, numerals and
     * capital letters.
     *
     * @param bool $secure
     *
     * @return $this
     */
    public function setSecure($secure)
    {
        if ($secure) {
            $this->method = 'random';
            $this->setNumerals(true);
            $this->setCapitalize(true);
        } else {
            $this->method = 'phonemes';
        }

        return $this;
    }

    /**
     * Include at least one number in the password. This is the default.
     *
     * @param bool $numerals
     *
     * @return $this
     */
    public function setNumerals($numerals)
    {
        if ($numerals) {
            $this->flags |= GeneratorInterface::FLAG_DIGITS;
        } else {
            $this->flags &= ~GeneratorInterface::FLAG_DIGITS;
        }

        return $this;
    }

    /**
     * Include at least one capital letter in the password. This is the default.
     *
     * @param bool $capitalize
     *
     * @return $this
     */
    public function setCapitalize($capitalize)
    {
        if ($capitalize) {
            $this->flags |= GeneratorInterface::FLAG_UPPERS;
        } else {
            $this->flags &= ~GeneratorInterface::FLAG_UPPERS;
        }

        return $this;
    }

    /**
     * Don't use characters that could be confused by the user when printed, such as 'l' and '1', or '0' or
     * 'O'. This reduces the number of possible passwords significantly, and as such reduces the quality of
     * the passwords. It may be useful for users who have bad vision, but in general use of this option is
     * not recommended.
     *
     * @param bool $ambiguous
     *
     * @return $this
     */
    public function setAmbiguous($ambiguous)
    {
        if ($ambiguous) {
            $this->flags |= GeneratorInterface::FLAG_AMBIGUOUS;
        } else {
            $this->flags &= ~GeneratorInterface::FLAG_AMBIGUOUS;
        }

        return $this;
    }

    /**
     * Generate random passwords that do not contain vowels or numbers that might be mistaken for vowels. It
     * provides less secure passwords to allow system administrators to not have to worry with random
     * passwords accidentally contain offensive substrings.
     *
     * @param bool $noVowels
     *
     * @return $this
     */
    public function setNoVowels($noVowels)
    {
        if ($noVowels) {
            $this->method = 'random';
            $this->flags |= GeneratorInterface::FLAG_NO_VOWELS | GeneratorInterface::FLAG_DIGITS | GeneratorInterface::FLAG_UPPERS;
        } else {
            $this->method = 'phonemes';
            $this->flags &= ~GeneratorInterface::FLAG_NO_VOWELS;
        }

        return $this;
    }

    /**
     * @param bool $symbols
     *
     * @return $this
     */
    public function setSymbols($symbols)
    {
        if ($symbols) {
            $this->flags |= GeneratorInterface::FLAG_SYMBOLS;
        } else {
            $this->flags &= ~GeneratorInterface::FLAG_SYMBOLS;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if ($this->method == 'phonemes') {
            $this->password = PhonemesPasswordGenerator::create($this->flags, $this->length)->generate();
        } else { // $this->method == 'random'
            $this->password = SecurePasswordGenerator::create($this->flags, $this->length)->generate();
        }

        return $this->password;
    }

    /**
     * Returns the last generated password. If there is none, a new one will be generated.
     *
     * @return string
     */
    public function __toString()
    {
        return (empty($this->password) ? $this->generate() : $this->password);
    }
}
