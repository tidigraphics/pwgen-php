<?php
namespace PWGen;

class PWElement
{
    public $str;
    public $flags;

    public function __construct($str, $flags)
    {
        $this->str   = $str;
        $this->flags = $flags;
    }
} 
