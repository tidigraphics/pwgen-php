<?php

namespace PWGen\Tests;

use PHPUnit\Framework\TestCase;
use PWGen\PasswordGenerator;

class PasswordGeneratorTest extends TestCase
{
    private $pwAmbiguous = 'B8G6I1l0OQDS5Z2';
    private $pwSymbols   = "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
    private $pwDigits    = '0123456789';
    private $pwUppers    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private $pwLowers    = 'abcdefghijklmnopqrstuvwxyz';
    private $pwVowels    = '01aeiouyAEIOUY';

    public function testSetCapitalize()
    {
        $generator = new PasswordGenerator();

        $generator->setCapitalize(true);

        $password = $generator->generate();

        $this->assertTrue($generator->hasCapitalize());
        $this->assertNotFalse(strpbrk($password, $this->pwUppers));
    }

    public function setLengthProvider()
    {
        return array(
            array(-1, 8),
            array(4, 4),
            array(2, 2),
            array(1, 1),
            array(20, 20),
        );
    }

    public function testSetSecure()
    {
        $generator = new PasswordGenerator();

        $generator->setSecure(true);

        $this->assertTrue($generator->isSecure());
    }


    public function testSetSymbols()
    {
        $generator = new PasswordGenerator();
        $generator->setSymbols(true);

        $password = $generator->generate();

        $this->assertNotFalse(strpbrk($password, $this->pwSymbols));
    }

    public function testSetNumerals()
    {
        $generator = new PasswordGenerator();
        $generator->setNumerals(true);
        $this->assertTrue($generator->hasNumerals());

        $password = $generator->generate();
        $this->assertNotFalse(strpbrk($password, $this->pwDigits));
    }


    public function testSetAmbiguous()
    {
        $generator = PasswordGenerator::create();
        $generator->setAmbiguous(true);

        $this->assertTrue($generator->hasAmbiguous());

        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $generator->setCapitalize(true);

        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $generator->setNumerals(true);

        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $generator->setSymbols(true);

        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }

        $generator->setSecure(true);

        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertFalse(strpbrk($password, $this->pwAmbiguous));
        }
    }

    /**
     * @dataProvider setLengthProvider
     */
    public function testSetLength($pwdLength, $expectedLength)
    {
        $generator = new PasswordGenerator();

        $generator->setLength($pwdLength);
        $this->assertEquals($expectedLength, $generator->getLength());


        for ($i = 0; $i < 10; $i++) {
            $password = $generator->generate();
            $this->assertEquals($expectedLength, strlen($password));
        }
        //if length < 5 it should use pw_rand
        $generator->setLength(4);

        //if length <= 2 there should not be any capitals
        $generator->setCapitalize(true);
        $generator->setLength(2);
        $password = $generator->generate();
        $this->assertFalse((bool)strpbrk($password, $this->pwUppers));

        //if length <= 1 there should not be any numerals
        $generator->setCapitalize(true);
        $generator->setNumerals(true);
        $generator->setLength(1);
        $password = $generator->generate();
        $this->assertFalse((bool)strpbrk($password, $this->pwDigits));

        $generator->setLength('bla');
        $password = $generator->generate();
        $this->assertEquals(8, strlen($password));

    }

    public function testSetNoVowels()
    {
        $generator = new PasswordGenerator();

        $generator->setNoVowels(true);

        $password = $generator->generate();

        $this->assertTrue($generator->hasNoVovels());
        $this->assertFalse(strpbrk($password, $this->pwVowels));
    }

    public function testToString()
    {
        $generator = new PasswordGenerator();
        $this->assertEquals($generator->generate(), (string)$generator);
    }


    public function testGenerateSecure()
    {
        $generator = new PasswordGenerator(20, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->isSecure());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertRegExp('/[A-Z]/', $pass); // Alpha upper
        $this->assertRegExp('/\\d/', $pass); // Numerals
    }

    public function testGenerateNumerals()
    {
        $generator = new PasswordGenerator(20, false, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasNumerals());
        $this->assertTrue($generator->hasCapitalize());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertRegExp('/[A-Z]/', $pass); // Alpha upper
        $this->assertRegExp('/\\d/', $pass); // Numerals
    }

    public function testGenerateNumeralsNoUppers()
    {
        $generator = new PasswordGenerator(20, false, true, false);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasNumerals());
        $this->assertFalse($generator->hasCapitalize());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertNotRegExp('/[A-Z]/', $pass); // Alpha NOT upper
        $this->assertRegExp('/\\d/', $pass); // Numerals
    }

    public function testGenerateCapitalize()
    {
        $generator = new PasswordGenerator(20, false, false, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasCapitalize());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertRegExp('/[A-Z]/', $pass); // Alpha upper
        $this->assertNotRegExp('/[\\d]/', $pass); // NO numerals!
    }

    public function testGenerateAmbiguous()
    {
        $generator = new PasswordGenerator(20, false, false, true, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasAmbiguous());
        $this->assertTrue($generator->hasCapitalize());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertRegExp('/[A-Z]/', $pass); // Alpha NOT upper
        $this->assertNotRegExp('/[\\d]/', $pass); // NO numerals!
    }

    public function testGenerateNoVovels()
    {
        $generator = new PasswordGenerator(20, false, false, false, false, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasNoVovels());
        $this->assertTrue($generator->hasNumerals());
        $this->assertTrue($generator->hasCapitalize());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertRegExp('/[A-Z]/', $pass); // Alpha upper
        $this->assertRegExp('/[\\d]/', $pass); // numerals
        $this->assertNotRegExp('/[' . preg_quote($generator->getGenerator()->getVowels(), '/') . ']/', $pass); // No Vovels
    }

    public function testGenerateSymbols()
    {
        $generator = new PasswordGenerator(20, false, false, false, false, false, true);

        $this->assertEquals(20, $generator->getLength());
        $this->assertTrue($generator->hasSymbols());

        $pass = $generator->generate();

        $this->assertInternalType('string', $pass);
        $this->assertEquals(20, strlen($pass));
        $this->assertRegExp('/[a-z]/', $pass); // Alpha lower
        $this->assertNotRegExp('/[A-Z]/', $pass); // Alpha NOT upper
        $this->assertNotRegExp('/[\\d]/', $pass); // NO numerals!
        $this->assertRegExp('/[' . preg_quote($generator->getGenerator()->getSymbols(), '/') . ']/', $pass); // Symbols
    }

    public function testBlacklistSymbol()
    {
        $generator = new PasswordGenerator();
        $generator->getGenerator()->blacklistSymbol(array('@', '#', '$'));

        $this->assertSame("!\"%&'()*+,-./:;<=>?[\]^_`{|}~", $generator->getGenerator()->getSymbols());
    }

    public function testGetAmbiguous()
    {
        $generator = new PasswordGenerator();
        $generator->setAmbiguous(true);

        $this->assertSame('B8G6I1l0OQDS5Z2', $generator->getGenerator()->getAmbiguous());
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
