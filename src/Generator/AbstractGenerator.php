<?php

namespace PWGen\Generator;

abstract class AbstractGenerator implements GeneratorInterface
{
    protected $ambiguous = 'B8G6I1l0OQDS5Z2';
    protected $symbols   = "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
    protected $digits    = '0123456789';
    protected $uppers    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $lowers    = 'abcdefghijklmnopqrstuvwxyz';
    protected $vowels    = '01aeiouyAEIOUY';

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var int
     */
    protected $lenth;
}
