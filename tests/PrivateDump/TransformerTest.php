<?php

use PrivateDump\Transformer;

class TransformerTest extends PHPUnit_Framework_TestCase
{
    /** @var Transformer */
    private $transformer;

    public function setUp()
    {
        $this->transformer = new Transformer(Faker\Factory::create('en_GB'));
    }

    /** @test */
    public function nonFakerTransformersWork()
    {
        $this->assertRegExp('/[a-zA-Z\s]+/', $this->transformer->transform('test', '@lorem'));
        $this->assertRegExp('/[a-zA-Z]+ [a-zA-z]+/', $this->transformer->transform('test', '@name'));

        $this->assertRegExp('/[0-9]{13}/', $this->transformer->transform('test', '@barcodeEan13'));
        $this->assertRegExp('/[0-9]{8}/', $this->transformer->transform('test', '@barcodeEan8'));
        $this->assertRegExp('/[0-9]{13}/', $this->transformer->transform('test', '@isbn13'));
        $this->assertRegExp('/[0-9A-Z]{10}/', $this->transformer->transform('test', '@isbn10'));

        $this->assertEquals('TEST', $this->transformer->transform('tEsT', '@uppercase'));
        $this->assertEquals('test', $this->transformer->transform('tEsT', '@lowercase'));

        $this->assertEquals('SURE THANG', $this->transformer->transform('yes', 'SURE THANG'));
        $this->assertRegExp(
            '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2}/',
            $this->transformer->transform('test', '@iso8601Recent')
        );
    }
}
