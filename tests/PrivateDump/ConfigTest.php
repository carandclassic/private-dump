<?php

use PrivateDump\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /** @var Config */
    private $config;
    private $filename = '';

    protected function setUp()
    {
        $this->filename = tempnam(sys_get_temp_dir(), 'private-dump');
        file_put_contents($this->filename, '{"mysql": {"username": "root", "password": "bigben", "hostname": "localhost"}, "databases": {"test": {}}}');

        $this->config = new Config($this->filename, ['mysql' => [
            'password' => 'expelliarmus'
        ]]);
        $this->config->parseConfig();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->filename);
    }

    public function testGet()
    {
        $this->assertEquals('expelliarmus', $this->config->get('mysql.password'));
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
}
