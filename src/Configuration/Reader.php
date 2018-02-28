<?php
/**
 * neuralyzer : Data Anonymization Library and CLI Tool
 *
 * PHP Version 7.0
 *
 * @author    Emmanuel Dyan
 * @author    Rémi Sauvat
 * @copyright 2017 Emmanuel Dyan
 *
 * @package   edyan/neuralyzer
 *
 * @license   GNU General Public License v2.0
 *
 * @link      https://github.com/edyan/neuralyzer
 */

namespace Inet\Neuralyzer\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration Reader
 */
class Reader
{
    /**
     * Configuration file name
     *
     * @var string
     */
    protected $configFileName;

    /**
     * Define the directories to search in. Only the first file found is taken into account
     * Can be defined via the constructor
     *
     * @var array
     */
    protected $configDirectories;

    /**
     * Configuration file name
     *
     * @var string|array
     */
    protected $configFilePath;

    /**
     * Stores the config values
     *
     * @var array
     */
    protected $configValues = [];

    /**
     * Constructor
     *
     * @param string $configFileName
     * @param array  $configDirectories
     */
    public function __construct(string $configFileName, array $configDirectories = ['.'])
    {
        $this->configFileName = $configFileName;
        $this->configDirectories = $configDirectories;

        $locator = new FileLocator($this->configDirectories);
        $this->configFilePath = $locator->locate($this->configFileName);

        $this->parseAndValidateConfig();
    }

    /**
     * Getter
     *
     * @return array Config Values
     */
    public function getConfigValues()
    {
        return $this->configValues;
    }

    /**
     * Return the list of entites
     *
     * @return array
     */
    public function getEntities(): array
    {
        return array_keys($this->configValues['entities']);
    }

    /**
     * Return the list of entites
     *
     * @return array
     */
    public function getPreQueries()
    {
        return $this->configValues['pre_queries'];
    }

    /**
     * Return the list of entites
     *
     * @return array
     */
    public function getPreDecryptQueries()
    {
        return $this->configValues['pre_decrypt_queries'];
    }

    /**
     * Return the list of entites
     *
     * @return array
     */
    public function getMysqlConfig()
    {
        return $this->configValues['mysql_config'];
    }

    /**
     * Return the list of entites
     *
     * @return array
     */
    public function getPostQueries()
    {
        return $this->configValues['post_queries'];
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->configValues['locale'];
    }

    /**
     * Parse and validate the configuration
     */
    protected function parseAndValidateConfig()
    {
        $this->configValues = Yaml::parse(file_get_contents($this->configFilePath));

        $processor = new Processor();
        $configuration = new AnonConfiguration();
        $processor->processConfiguration($configuration, [$this->configValues]);
    }
}
