<?php

namespace PrivateDump;

use Faker\Generator;
use PhpParser\Node\Param;

class Transformer
{
    public static $booted = false;
    private $objectCache = [];
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
     * Forget cached values
     */
    public function forget()
    {
        $this->objectCache = [];
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

    protected function transformObjectUser()
    {
        $user = new \stdClass();

        $user->firstName = $this->faker->firstName();
        $user->lastName = $this->faker->lastName();
        $user->email = sprintf('%s.%s@example.com', mb_strtolower($user->firstName), mb_strtolower($user->lastName));
        $user->userName = $user->email;
        $user->fullName = "{$user->firstName} {$user->lastName}";

        return $user;
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


        // Check if replacement matches the "@replacementObject(name).accessor" format,
        // in which case a named object will be placed in the object cache.

        $object = null;
        $matches = null;
        if (preg_match('/^@(\w+)\((\w+\))\.(.*)$/', $replacement, $matches)) {
            $objectType = $matches[1];
            $objectName = $matches[2];

            if (array_key_exists($objectName, $this->objectCache)) {
                $object = $this->objectCache[$objectName];
            } else {
                $getObjectMethod = sprintf('transformObject%s', ucwords(strtolower($objectType)));
                if (method_exists($this, $getObjectMethod)) {
                    $object = $this->$getObjectMethod();
                    $this->objectCache[$objectName] = $object;
                }
            }

            // Replace with only the accessor part
            $replacement = "@{$matches[3]}";
        }

        // Faker Transformer has modifiers, let's use them
        if (strpos($replacement, '|') !== false) {
            [$replacement, $modifiers] = explode('|', $replacement, 2);
            $modifiers = explode(',', $modifiers);
        }

        $replacement = preg_replace('/^@/', '', $replacement);
        $originalReplacement = $replacement;

        if ($object === null && array_key_exists($replacement, $this->transformerAliases)) {
            $replacement = $this->transformerAliases[$replacement];
        }

        $ownMethod = sprintf('transform%s', ucwords(strtolower($replacement)));

        try {
            if ($object !== null) {
                $newValue = $object->$replacement;
            } else {
                $newValue = method_exists($this, $ownMethod)
                    ? $this->$ownMethod($value, ...$modifiers)
                    : $this->faker->$replacement(...$modifiers);
            }
        } catch (\Exception $e) {
            echo sprintf('[error] Transformer not found, please fix and retry: [%s]', $originalReplacement).PHP_EOL;
            exit(9);
        }

        return $newValue;
    }
}
