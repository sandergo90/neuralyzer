<?php

namespace Inet\Neuralyzer\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfigLoader
 *
 * @package Inet\Neuralyzer\Loader
 */
class YamlConfigLoader extends FileLoader
{
    /** @var string */
    private $parsed;

    public function load($resource, $type = null)
    {
        $values = Yaml::parse(file_get_contents($resource));

        $this->parseImports($values, $resource);

        if (!isset($values['imports'])) {
            $this->parsed = $values;
        }

        if (isset($values['imports'])) {
            unset($values['imports']);
            $values = array_merge($this->parsed, $values);
        }

        return $values;
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
