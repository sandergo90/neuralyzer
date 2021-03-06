<?php
/**
 * neuralyzer : Data Anonymization Library and CLI Tool
 *
 * PHP Version 7.0
 *
 * @author Emmanuel Dyan
 * @author Rémi Sauvat
 * @copyright 2017 Emmanuel Dyan
 *
 * @package edyan/neuralyzer
 *
 * @license GNU General Public License v2.0
 *
 * @link https://github.com/edyan/neuralyzer
 */

namespace Inet\Neuralyzer\Configuration;

use Faker\Factory;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration Validation
 */
class AnonConfiguration implements ConfigurationInterface
{
    /**
     * Validate the configuration
     *
     * The config structure is something like :
     * ## Root
     * entities:
     *    ## Can be repeated : the name of the table, is an array
     *    accounts:
     *        cols:
     *            ## Can be repeated : the name of the field, is an array
     *            name:
     *                method: words # Required: name of the method
     *                params: [8] # Optional: parameters (an array)
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');
        $rootNode
            ->children()
                ->scalarNode('guesser_version')->isRequired()->end()
                ->scalarNode('locale')->defaultValue(Factory::DEFAULT_LOCALE)->end()
                ->scalarNode('charset')->defaultValue('utf8')->end()
                ->scalarNode('collate')->defaultValue('utf8_unicode_ci')->end()
                ->arrayNode('pre_decrypt_queries')
                    ->defaultValue(array())
                    ->normalizeKeys(false)
                    ->info('The list of queries to execute before decrypting')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('pre_queries')
                    ->defaultValue(array())
                    ->normalizeKeys(false)
                    ->info('The list of queries to execute before anonymize')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('entities')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('cols')
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('method')->isRequired()->end()
                                        ->scalarNode('unique')->defaultFalse()->end()
                                        ->arrayNode('params')
                                            ->requiresAtLeastOneElement()->prototype('variable')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('delete')->defaultValue(false)->end()
                            ->scalarNode('delete_where')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('post_queries')
                    ->defaultValue(array())
                    ->normalizeKeys(false)
                    ->info('The list of queries to execute after anonymize')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
