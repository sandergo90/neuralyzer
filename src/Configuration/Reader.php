<?php
/**
 * Inet Data Anonymization
 *
 * PHP Version 5.3 -> 7.0
 *
 * @author    Emmanuel Dyan
 * @author    Rémi Sauvat
 * @copyright 2005-2015 iNet Process
 *
 * @package   inetprocess/neuralyzer
 *
 * @license   GNU General Public License v2.0
 *
 * @link      http://www.inetprocess.com
 */

namespace Inet\Neuralyzer\Configuration;

use Inet\Neuralyzer\Loader\YamlConfigLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

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
     * @var FileLocator
     */
    private $locator;

    /**
     * Constructor
     *
     * @param string $configFileName
     * @param array  $configDirectories
     */
    public function __construct($configFileName, array $configDirectories = ['.'])
    {
        $this->configFileName = $configFileName;
        $this->configDirectories = $configDirectories;

        $this->locator = new FileLocator($this->configDirectories);
        $this->configFilePath = $this->locator->locate($this->configFileName);

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
    public function getEntities()
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
     * @return mixed
     */
    public function getCharset()
    {
        return $this->configValues['charset'];
    }

    /**
     * @return mixed
     */
    public function getCollate()
    {
        return $this->configValues['collate'];
    }

    /**
     * Parse and validate the configuration
     */
    protected function parseAndValidateConfig()
    {
        $loaderResolver = new LoaderResolver(array(new YamlConfigLoader($this->locator)));
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        $values = $delegatingLoader->load($this->configFilePath);

        $processor = new Processor();
        $configuration = new AnonConfiguration();
        $this->configValues = $processor->processConfiguration($configuration, [$values]);
    }
}
