<?php

namespace Inet\Neuralyzer\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfigLoader
 *
 * @package Inet\Neuralyzer\Loader
 */
class YamlConfigLoader extends FileLoader
{
    /** @var array */
    private $parsed = [];

    /** @var YamlParser */
    private $yamlParser;

    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        $content = $this->yamlParser->parseFile($path, Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS);

        // empty file
        if (null === $content) {
            return;
        }

        // parameters
        $this->parsed = array_merge_recursive($this->parsed, $content);

        // imports
        $this->parseImports($content, $path);

        unset($this->parsed['imports']);

        return $this->parsed;
    }

    /**
     * Parses all imports.
     *
     * @param array  $content
     * @param string $file
     */
    private function parseImports(array $content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        $defaultDirectory = dirname($file);
        foreach ($content['imports'] as $import) {
            if (!is_array($import)) {
                $import = ['resource' => $import];
            }
            if (!isset($import['resource'])) {
                throw new InvalidArgumentException(sprintf('An import should provide a resource in %s. Check your YAML syntax.', $file));
            }

            $this->setCurrentDir($defaultDirectory);
            $this->import(
                $import['resource'],
                isset($import['type']) ? $import['type'] : null,
                isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false,
                $file
            );
        }
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
                $resource,
                PATHINFO_EXTENSION
            );
    }
}
