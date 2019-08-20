<?php

namespace PWGen\Generator;

abstract class AbstractGenerator implements GeneratorInterface
{
    protected $ambiguous = 'B8G6I1l0OQDS5Z2';
    protected $symbols = "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
    protected $digits = '0123456789';
    protected $uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $lowers = 'abcdefghijklmnopqrstuvwxyz';
    protected $vowels = '01aeiouyAEIOUY';

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var int
     */
    protected $length;

    /**
     * @return string
     */
    public function getAmbiguous()
    {
        return $this->ambiguous;
    }

    /**
     * @return string
     */
    public function getSymbols()
    {
        return $this->symbols;
    }

    /**
     * @return string
     */
    public function getVowels()
    {
        return $this->vowels;
    }

    /**
     * Disallow certain special chars
     * @param array $symbols
     */
    public function blacklistSymbol(array $symbols)
    {
        foreach ($symbols as $symbol) {
            if (!preg_match('/' . preg_quote($symbol) . '/', $this->symbols)) {
                return;
            }
        }

        $symbolArray = str_split($this->symbols);

        foreach ($symbols as $symbol) {
            $index =array_search($symbol, $symbolArray);
            unset($symbolArray[$index]);
        }

        $this->symbols = implode('', $symbolArray);
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
}


