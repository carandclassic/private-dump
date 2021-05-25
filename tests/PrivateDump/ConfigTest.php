<?php

use PHPUnit\Framework\TestCase;
use PrivateDump\Config;

class ConfigTest extends TestCase
{
    /** @var Config */
    private $config;
    private $filename = '';

    /** @test */
    public function weCanOverrideConnectionConfig()
    {
        $this->getConfig('{"connection": {"username": "root", "password": "bigben", "hostname": "localhost"}, "databases": {"test": {}}}', ['connection' => [
            'password' => 'expelliarmus'
        ]]);

        $this->assertEquals('root', $this->config->get('connection.username'));
        $this->assertEquals('expelliarmus', $this->config->get('connection.password'));
        $this->assertEquals('localhost', $this->config->get('connection.hostname'));
    }

    /** @test */
    public function weGetAnErrorWithABadFile()
    {
        $this->assertFalse((new Config('/non/existent/file/ever.json'))->isValid());
        $this->assertFalse($this->getConfig('BAD JSON')->isValid());

        // No databases
        $this->assertFalse($this->getConfig('{"connection": {"username": "root", "password": "bigben", "hostname": "localhost"}')->isValid());
    }

    public function testGet()
    {
        $this->assertEquals('bigben', $this->config->get('connection.password'));
        $this->assertEquals('localhost', $this->config->get('connection.hostname'));
        $this->assertEquals('root', $this->config->get('connection.username'));
    }

    public function testGetDSN()
    {
        $expected = 'mysql:host=localhost;dbname=database';
        $this->assertEquals($expected, $this->config->getDSN('database'));
    }

    public function testIsValid()
    {
        $this->assertTrue($this->config->isValid());
    }


    protected function setUp(): void
    {
        $this->getConfig('{"connection": {"username": "root", "password": "bigben", "hostname": "localhost"}, "databases": {"test": {}}}');
        parent::setUp();
    }

    protected function getConfig($json, $overrides=[])
    {
        $this->filename = tempnam(sys_get_temp_dir(), 'private-dump');
        file_put_contents($this->filename, $json);

        $this->config = new Config($this->filename, $overrides);
        $this->config->parseConfig();

        return $this->config;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink($this->filename);
    }
}
