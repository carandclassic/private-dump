<?php

namespace PrivateDump;

use Faker\Generator;

class Transformer
{
    private $faker;
    private $transformerAliases = [
        'lorem' => 'sentence',
        'fullName' => 'name',
        'fullAddress' => 'address',
        'loremSentence' => 'sentence',
        'loremParagraph' => 'paragraph',
        'loremParagraphs' => 'paragraphs',
        'randomString' => 'string',
        'county' => 'state',
        'username' => 'userName',
        'barcodeEan13' => 'ean13',
        'barcodeEan8' => 'ean8',
        'barcodeIsbn13' => 'isbn13',
        'barcodeIsbn10' => 'isbn10',
        'email' => 'safeEmail',
    ];

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Generate random string
     *
     * @param string $value
     * @return bool|string
     */
    public function transformString($value)
    {
        $string = '';
        $length = rand(10, 255);

        while ($currentLength = strlen($string) < $length) {
            $string .= uniqid();
        }

        return substr($string, 0, $length);
    }

    /**
     * @param string $value
     * @return string
     */
    public function transformUppercase($value)
    {
        return strtoupper($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function transformLowercase($value)
    {
        return strtolower($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function transformIso8601Recent($value)
    {
        return $this->faker->dateTimeBetween('-3 months')->format(\DateTime::ATOM);
    }

    /**
     * Transform given value based on the replacement string provided from the JSON
     * @param $value
     * @param $replacement
     * @return mixed
     */
    public function transform($value, $replacement)
    {
        $replacement = preg_replace('/^@/', '', $replacement);
        $originalReplacement = $replacement;

        if (array_key_exists($replacement, $this->transformerAliases)) {
            $replacement = $this->transformerAliases[$replacement];
        }

        $ownMethod = sprintf('transform%s', ucwords(strtolower($replacement)));
        if (method_exists($this, $ownMethod)) {
            return $this->$ownMethod($value);
        }

        try {
            $newValue = $this->faker->$replacement;
        } catch (\Exception $e) {
            echo sprintf('[error] Transformer not found, please fix and retry: [%s]', $originalReplacement) . PHP_EOL;
            exit(9);
        }

        return $newValue;
    }
}