<?php

namespace Inet\Neuralyzer\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Class YamlConfigLoader
 *
 * @package Inet\Neuralyzer\Loader
 */
class YamlConfigLoader extends FileLoader
{
    const PARSE_CONSTANT = 256;

    const PARSE_CUSTOM_TAGS = 512;

    /** @var array */
    private $parsed = array();

    /** @var YamlParser */
    private $yamlParser;

    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        $content = $this->parseFile($path, self::PARSE_CONSTANT | self::PARSE_CUSTOM_TAGS);

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
     * Parses a YAML file into a PHP value.
     *
     * @param string $filename The path to the YAML file to be parsed
     * @param int    $flags    A bit field of PARSE_* constants to customize the YAML parser behavior
     *
     * @return mixed The YAML converted to a PHP value
     *
     * @throws ParseException If the file could not be read or the YAML is not valid
     */
    private function parseFile($filename, $flags = 0)
    {
        if (!is_file($filename)) {
            throw new ParseException(sprintf('File "%s" does not exist.', $filename));
        }

        if (!is_readable($filename)) {
            throw new ParseException(sprintf('File "%s" cannot be read.', $filename));
        }

        $this->filename = $filename;

        try {
            return $this->yamlParser->parse(file_get_contents($filename), $flags);
        } finally {
            $this->filename = null;
        }
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
