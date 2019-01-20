<?php
namespace PrivateDump;

use Dflydev\DotAccessData\Data;

class Config
{
    private $filename;
    private $error;

    /** @var Data */
    private $config;
    private $overrides = [];
    private $mysqlConfigRequired = [
        'username',
        'password',
        'hostname',
    ];

    /**
     * @param string $filename
     * @param array $overrides
     */
    public function __construct($filename, array $overrides = [])
    {
        $this->filename = $filename;
        $this->overrides = $overrides;
    }

    /**
     * @param string $key
     * @param string $default
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * @param string $databaseName
     * @return string
     */
    public function getDSN($databaseName)
    {
        return sprintf('mysql:host=%s;dbname=%s', $this->get('mysql.hostname'), $databaseName);
    }

    /**
     * Read the filename and set the config param*
     * @return bool
     */
    public function parseConfig()
    {
        $fileContents = file_get_contents($this->filename);
        if (!$fileContents) {
            $this->error = 'Failed to read file contents';
            return false;
        }

        $config = json_decode($fileContents, true);
        if (json_last_error()) {
            $this->error = json_last_error_msg();
            return false;
        }

        $this->config = new Data(array_replace_recursive($config, $this->overrides));

        return true;
    }
    /**
     * Is the config valid?
     *
     * @return bool
     */
    public function isValid()
    {
        if (!file_exists($this->filename)) {
            $this->error = 'File does not exist';
            return false;
        }

        if (!is_readable($this->filename)) {
            $this->error = 'File is not readable';
            return false;
        }

        if (!$this->parseConfig()) {
            return false;
        }

        foreach ($this->mysqlConfigRequired as $configKeyRequired) {
            if (!array_key_exists($configKeyRequired, $this->config->get('mysql')) || is_null($this->config->get('mysql.'.$configKeyRequired))) {
                $this->error = sprintf('MySQL config key missing or null: %s', $configKeyRequired);
            }
        }

        $numberOfDatabases = count($this->get('databases', []));

        if ($numberOfDatabases === 0) {
            $this->error = 'No database configuration provided.  Cannot continue.';
            return false;
        }

        if (!empty($this->error)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
