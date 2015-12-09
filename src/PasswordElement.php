<?php
namespace PWGen;

/**
 * Class PasswordElement
 *
 */
final class PasswordElement
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var int
     */
    private $flags;

    /**
     * PasswordElement constructor.
     *
     * @param string $string
     * @param int    $flags
     */
    public function __construct($string, $flags)
    {
        $this->string = $string;
        $this->flags  = $flags;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }
}
