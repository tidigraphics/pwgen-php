<?php
namespace PWGen\Generator;

interface GeneratorInterface
{
    /**
     * Flags for the generator function
     */
    const FLAG_DIGITS    = 0x0001;
    const FLAG_UPPERS    = 0x0002; // At least one upper letter
    const FLAG_SYMBOLS   = 0x0004;
    const FLAG_AMBIGUOUS = 0x0008;

    /**
     * Flags for the password elements
     */
    const FLAG_CONSONANT = 0x0001;
    const FLAG_VOWEL     = 0x0002;
    const FLAG_DIPHTHONG = 0x0004;
    const FLAG_NOT_FIRST = 0x0008;
    const FLAG_NO_VOWELS = 0x0010;

    /**
     * Generates a password
     *
     * @return string
     */
    public function generate();

    /**
     * @return string
     */
    public function getAmbiguous();

    /**
     * @return string
     */
    public function getSymbols();

    /**
     * @return string
     */
    public function getVowels();

    /**
     * @return int
     */
    public function getLength();

    /**
     * @return int
     */
    public function getFlags();
}
