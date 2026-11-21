<?php

use PHPUnit\Framework\TestCase;
use TotpPhp\Base32;

class Base32Test extends TestCase
{
    protected $base32;

    protected function setUp(): void
    {
        $this->base32 = new Base32();
    }

    public function testBase32Decode()
    {
        $encoded = 'JBSWY3DP';
        $decoded = $this->base32->_base32Decode($encoded);

        $this->assertNotFalse($decoded);
        $this->assertEquals('Hello', $decoded);
    }

    public function testBase32DecodeRfc4648Vectors()
    {
        $vectors = [
            'MY======' => 'f',
            'MZXQ====' => 'fo',
            'MZXW6===' => 'foo',
            'MZXW6YQ=' => 'foob',
            'MZXW6YTB' => 'fooba',
            'MZXW6YTBOI======' => 'foobar',
        ];

        foreach ($vectors as $encoded => $expected) {
            $this->assertSame($expected, $this->base32->_base32Decode($encoded));
        }
    }

    public function testBase32DecodeInvalid()
    {
        $invalidValues = [
            'INVALIDBAS2251STRING',
            '0',
            'M=ZXW6==',
            'MZXW6====',
            'A',
        ];

        foreach ($invalidValues as $invalidValue) {
            $this->assertFalse(
                $this->base32->_base32Decode($invalidValue),
                'Base32 decoding of an invalid string should return false.'
            );
        }
    }


    public function testGetBase32LookupTable()
    {
        $lookupTable = $this->base32->_getBase32LookupTable();
        $this->assertIsArray($lookupTable);
        $this->assertCount(33, $lookupTable);
    }
}
