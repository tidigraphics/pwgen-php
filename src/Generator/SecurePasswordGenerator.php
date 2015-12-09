<?php

namespace PWGen\Generator;

/**
 * Class SecurePasswordGenerator
 *
 * @package PWGen\Generator
 */
class SecurePasswordGenerator extends AbstractGenerator
{
    /**
     * @var array
     */
    private $temporaryPassword;


    /**
     * SecurePasswordGenerator constructor.
     *
     * @param int $flags
     * @param int $length
     */
    public function __construct($flags, $length)
    {
        $this->flags  = $flags;
        $this->length = $length;
    }

    /**
     * @param int $flags
     * @param int $length
     *
     * @return SecurePasswordGenerator
     */
    public static function create($flags, $length)
    {
        return new self($flags, $length);
    }

    /**
     * Generates a truly random password
     */
    public function generate()
    {
        $this->temporaryPassword = array();

        $chars = '';
        if ($this->flags & GeneratorInterface::FLAG_DIGITS) {
            $chars .= $this->digits;
        }
        if ($this->flags & GeneratorInterface::FLAG_UPPERS) {
            $chars .= $this->uppers;
        }
        $chars .= $this->lowers;
        if ($this->flags & GeneratorInterface::FLAG_SYMBOLS) {
            $chars .= $this->symbols;
        }

        do {
            $len            = strlen($chars);
            $generatorFlags = $this->flags;
            $i              = 0;

            while ($i < $this->length) {
                $ch = $chars[random_int(0, $len - 1)];
                if (($this->flags & GeneratorInterface::FLAG_AMBIGUOUS) &&
                    strpos($this->ambiguous, $ch) !== false
                ) {
                    continue;
                }
                if (($this->flags & GeneratorInterface::FLAG_NO_VOWELS) &&
                    strpos($this->vowels, $ch) !== false
                ) {
                    continue;
                }
                $this->temporaryPassword[$i++] = $ch;
                if (strpos($this->digits, $ch) !== false) {
                    $generatorFlags &= ~GeneratorInterface::FLAG_DIGITS;
                }
                if (strpos($this->uppers, $ch) !== false) {
                    $generatorFlags &= ~GeneratorInterface::FLAG_UPPERS;
                }
                if (strchr($this->symbols, $ch) !== false) {
                    $generatorFlags &= ~GeneratorInterface::FLAG_SYMBOLS;
                }
            }
        } while ($generatorFlags & (GeneratorInterface::FLAG_UPPERS | GeneratorInterface::FLAG_DIGITS | GeneratorInterface::FLAG_SYMBOLS));

        return implode('', $this->temporaryPassword);
    }
}
