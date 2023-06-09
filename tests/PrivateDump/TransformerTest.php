<?php

use PHPUnit\Framework\TestCase;
use PrivateDump\Transformer;

class TransformerTest extends TestCase
{
    /** @var Transformer */
    private $transformer;

    public function setUp(): void
    {
        $this->transformer = new Transformer(Faker\Factory::create('en_GB'));
        parent::setUp();
    }

    /** @test */
    public function nonFakerTransformersWork()
    {
        $this->assertMatchesRegularExpression('/[a-zA-Z\s]+/', $this->transformer->transform('test', '@lorem'));
        $this->assertMatchesRegularExpression('/[a-zA-Z]+ [a-zA-z]+/', $this->transformer->transform('test', '@name'));

        $this->assertMatchesRegularExpression('/[0-9]{13}/', $this->transformer->transform('test', '@barcodeEan13'));
        $this->assertMatchesRegularExpression('/[0-9]{8}/', $this->transformer->transform('test', '@barcodeEan8'));
        $this->assertMatchesRegularExpression('/[0-9]{13}/', $this->transformer->transform('test', '@isbn13'));
        $this->assertMatchesRegularExpression('/[0-9A-Z]{10}/', $this->transformer->transform('test', '@isbn10'));

        $this->assertEquals('TEST', $this->transformer->transform('tEsT', '@uppercase'));
        $this->assertEquals('test', $this->transformer->transform('tEsT', '@lowercase'));

        $this->assertEquals('SURE THANG', $this->transformer->transform('yes', 'SURE THANG'));
        $this->assertMatchesRegularExpression(
            '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2}/',
            $this->transformer->transform('test', '@iso8601Recent')
        );
    }

    /** @test */
    public function avatar_url_works()
    {
        $transformed = $this->transformer->transform('not required', '@avatarUrl');
        $this->assertMatchesRegularExpression("/https\:\/\/www\.gravatar\.com\/avatar\/(.*)\?d=(.*)/", $transformed);
    }

    /** @test */
    public function fake_car_faker_plugin_works()
    {
        $vehicleArray = $this->transformer->transform('not required', '@vehicleArray');
        $this->assertArrayHasKey('brand', $vehicleArray);
        $this->assertArrayHasKey('model', $vehicleArray);
    }

    /** @test */
    public function modifiers_work()
    {
        $this->assertEquals(10, $this->transformer->transform('test', '@numberBetween|10,10'));
    }

    /** @test */
    public function static_values_work()
    {
        $this->assertEquals('replacementValue', $this->transformer->transform('test', 'replacementValue'));
    }

    /** @test */
    public function original_replacement_works()
    {
        $this->assertEquals('admin@example.com', $this->transformer->transform('admin@example.com', '@original'));
        $this->assertEquals('admin@exa', $this->transformer->transform('admin@example.com', '@original|9'));
    }

    /** @test */
    public function seeder_works()
    {
        $this->transformer->seed(123);

        $firstEmail = $this->transformer->transform('some@example.com', '@email');
        $secondEmail = $this->transformer->transform('some@example.com', '@email');

        $this->transformer->seed(123);
        $thirdEmail = $this->transformer->transform('some@example.com', '@email');
        $fourthEmail = $this->transformer->transform('some@example.com', '@email');

        $this->assertEquals($firstEmail, $thirdEmail);
        $this->assertEquals($secondEmail, $fourthEmail);
    }

    /** @test */
    public function object_works()
    {
        $user1Email = $this->transformer->transform('', '@user(user1).email');
        $user1FirstName = $this->transformer->transform('', '@user(user1).firstName');
        $user1LastName = $this->transformer->transform('', '@user(user1).lastName');

        $user2Email = $this->transformer->transform('', '@user(user2).email');

        $this->assertStringStartsWith(sprintf('%s.%s-', mb_strtolower($user1FirstName), mb_strtolower($user1LastName)), $user1Email);

        $this->assertNotEquals($user1Email, $user2Email);
    }
}
