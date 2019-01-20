<?php

use PrivateDump\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /** @var Config */
    private $config;
    private $filename = '';

    /** @test */
    public function weCanOverrideMySQLConfig()
    {
        $this->getConfig('{"mysql": {"username": "root", "password": "bigben", "hostname": "localhost"}, "databases": {"test": {}}}', ['mysql' => [
            'password' => 'expelliarmus'
        ]]);

        $this->assertEquals('expelliarmus', $this->config->get('mysql.password'));
    }

    /** @test */
    public function weGetAnErrorWithABadFile()
    {
        $this->assertFalse((new Config('/non/existent/file/ever.json'))->isValid());
        $this->assertFalse($this->getConfig('BAD JSON')->isValid());

        // No databases
        $this->assertFalse($this->getConfig('{"mysql": {"username": "root", "password": "bigben", "hostname": "localhost"}')->isValid());
    }

    public function testGet()
    {
        $this->assertEquals('bigben', $this->config->get('mysql.password'));
        $this->assertEquals('localhost', $this->config->get('mysql.hostname'));
        $this->assertEquals('root', $this->config->get('mysql.username'));
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


    protected function setUp()
    {
        $this->getConfig('{"mysql": {"username": "root", "password": "bigben", "hostname": "localhost"}, "databases": {"test": {}}}');
    }

    protected function getConfig($json, $overrides=[])
    {
        $this->filename = tempnam(sys_get_temp_dir(), 'private-dump');
        file_put_contents($this->filename, $json);

        $this->config = new Config($this->filename, $overrides);
        $this->config->parseConfig();

        return $this->config;
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->filename);
    }
}
