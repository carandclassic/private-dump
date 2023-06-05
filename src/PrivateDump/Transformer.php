<?php

namespace PrivateDump;

use Faker\Generator;
use PhpParser\Node\Param;

class Transformer
{
    public static $booted = false;
    private $faker;
    private $transformerAliases = [
        'lorem'           => 'sentence',
        'fullName'        => 'name',
        'fullAddress'     => 'address',
        'loremSentence'   => 'sentence',
        'loremParagraph'  => 'paragraph',
        'loremParagraphs' => 'paragraphs',
        'randomString'    => 'string',
        'county'          => 'state',
        'username'        => 'userName',
        'barcodeEan13'    => 'ean13',
        'barcodeEan8'     => 'ean8',
        'barcodeIsbn13'   => 'isbn13',
        'barcodeIsbn10'   => 'isbn10',
        'email'           => 'safeEmail',
    ];

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
        if (!self::$booted) {
            $this->boot($faker);
        }
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));
    }

    protected function boot(Generator $faker)
    {
        self::$booted = true;
    }

    /**
     * Seed the faker library.
     *
     * @param int $value
     */
    public function seed($value)
    {
        $this->faker->seed($value);
    }

    /**
     * Generate random string.
     *
     * @param string $value
     *
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
     *
     * @return string
     */
    public function transformUppercase($value)
    {
        return strtoupper($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function transformLowercase($value)
    {
        return strtolower($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function transformIso8601Recent($value)
    {
        return $this->faker->dateTimeBetween('-3 months')->format(\DateTime::ATOM);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function transformOriginal($value, ?int $max = null)
    {
        if ($max) {
            return substr($value, 0, $max);
        }

        return $value;
    }

    public function transformAvatarUrl($value)
    {
        return sprintf(
            'https://www.gravatar.com/avatar/%s?d=%s',
            md5(strtolower($this->faker->email)),
            $this->faker->randomElement([
                'identicon',
                'monsterid',
                'mp',
                'robohash',
            ])
        );
    }

    /**
     * Transform given value based on the replacement string provided from the JSON.
     *
     * @param string $value
     * @param string $replacement
     *
     * @return mixed
     */
    public function transform($value, $replacement)
    {
        $modifiers = [];
        // Doesn't start with @, just return the value in the config
        if (strpos($replacement, '@') !== 0) {
            return $replacement;
        }

        // Faker Transformer has modifiers, let's use them
        if (strpos($replacement, '|') !== false) {
            [$replacement, $modifiers] = explode('|', $replacement, 2);
            $modifiers = explode(',', $modifiers);
        }

        $replacement = preg_replace('/^@/', '', $replacement);
        $originalReplacement = $replacement;

        if (array_key_exists($replacement, $this->transformerAliases)) {
            $replacement = $this->transformerAliases[$replacement];
        }

        $ownMethod = sprintf('transform%s', ucwords(strtolower($replacement)));

        try {
            $newValue = method_exists($this, $ownMethod)
                ? $this->$ownMethod($value, ...$modifiers)
                : $this->faker->$replacement(...$modifiers);
        } catch (\Exception $e) {
            echo sprintf('[error] Transformer not found, please fix and retry: [%s]', $originalReplacement).PHP_EOL;
            exit(9);
        }

        return $newValue;
    }
}
