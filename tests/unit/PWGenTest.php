<?php

namespace PWGen\Tests;

use PWGen\PWGen;

class PWGenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PWGen
     */
    private $pwGen;

    private $pwAmbiguous = 'B8G6I1l0OQDS5Z2';
    private $pwSymbols   = "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
    private $pwDigits    = '0123456789';
    private $pwUppers    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private $pwLowers    = 'abcdefghijklmnopqrstuvwxyz';
    private $pwVowels    = '01aeiouyAEIOUY';

    public function setUp()
    {
        $this->pwGen = new PWGen();
    }

    public function testSetSymbols()
    {
        $this->pwGen->setSymbols(true);

        $password = $this->pwGen->generate();

        $this->assertNotFalse(strpbrk($password, $this->pwSymbols));
    }

    public function testSetNumerals()
    {
        $this->pwGen->setNumerals(true);

        $password = $this->pwGen->generate();

        $this->assertNotFalse(strpbrk($password, $this->pwDigits));
    }

    public function testSetCapitalize()
    {
        $this->pwGen->setCapitalize(true);

        $password = $this->pwGen->generate();

        $this->assertNotFalse(strpbrk($password, $this->pwUppers));
    }

    public function testSetAmbiguous()
    {
        $this->pwGen = new PWGen();
        $this->pwGen->setAmbiguous(true);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $this->pwGen->setCapitalize(true);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $this->pwGen->setNumerals(true);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $this->pwGen->setSymbols(true);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $this->pwGen->setSecure(true);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }
    }

    public function testSetLength()
    {
        $this->pwGen->setLength(8);

        for ($i =0; $i<10; $i++) {
            $password = $this->pwGen->generate();
            $this->assertEquals(8, strlen($password));
        }
        //if length < 5 it should use pw_rand
        $this->pwGen->setLength(4);

        //if length <= 2 there should not be any capitals
        $this->pwGen->setCapitalize(true);
        $this->pwGen->setLength(2);
        $password = $this->pwGen->generate();
        $this->assertFalse((bool) strpbrk($password, $this->pwUppers));

        //if length <= 1 there should not be any numerals
        $this->pwGen->setCapitalize(true);
        $this->pwGen->setNumerals(true);
        $this->pwGen->setLength(1);
        $password = $this->pwGen->generate();
        $this->assertFalse((bool) strpbrk($password, $this->pwDigits));

        $this->pwGen->setLength('bla');
        $password = $this->pwGen->generate();
        $this->assertEquals(8, strlen($password));

    }

    public function testSetNoVowels()
    {
        $this->pwGen->setNoVowels(true);

        $password = $this->pwGen->generate();

        $this->assertFalse(strpbrk($password, $this->pwVowels));
    }

    public function test__ToString()
    {
        $this->assertEquals($this->pwGen->generate(), (string)$this->pwGen);
    }

    /**
     * @param string $string
     * @param array  $array
     *
     * @return bool
     */
    private function stringContainsStringFromArray($string, $array)
    {
        if (0 < count(array_intersect(str_split($string), $array))) {
            return true;
        }

        return false;
    }
}
