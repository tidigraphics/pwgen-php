<?php
namespace PWGen;

class PWElement
{
    public $str;
    public $flags;

    /**
     * PWElement constructor.
     *
     * @param string $str
     * @param int    $flags
     */
    public function __construct($str, $flags)
    {
        $this->str   = $str;
        $this->flags = $flags;
    }
} 
