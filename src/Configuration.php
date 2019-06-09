<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration
{
    public function configure(ArrayNodeDefinition $node)
    {
        $this->configureCoverageSection($node);
        $this->configureSessionSection($node);
        $this->configureReportSection($node);
        $this->configureFilterSection($node);
    }

    private function configureCoverageSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->booleanNode('xdebug_patch')->defaultTrue()->end()
                ->arrayNode('coverage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('processUncoveredFilesFromWhitelist')->defaultFalse()->end()
                        ->booleanNode('checkForUnintentionallyCoveredCode')->defaultFalse()->end()
                        ->booleanNode('forceCoversAnnotation')->defaultFalse()->end()
                        ->booleanNode('checkForMissingCoversAnnotation')->defaultFalse()->end()
                        ->booleanNode('checkForUnexecutedCoveredCode')->defaultFalse()->end()
                        ->booleanNode('addUncoveredFilesFromWhitelist')->defaultTrue()->end()
                        ->booleanNode('disableIgnoredLines')->defaultFalse()->end()
                        ->booleanNode('ignoreDeprecatedCode')->defaultFalse()->end()
                        ->arrayNode('unintentionallyCoveredSubclassesWhitelist')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure remote section.
     *
     * @return ArrayNodeDefinition
     */
    private function configureSessionSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('sessions')
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('driver')
                                ->values(['local', 'remote'])
                                ->defaultValue('local')
                            ->end()
                            ->scalarNode('remote_url')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function configureReportSection(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('report')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addOptionsNode('clover'))
                        ->append($this->addOptionsNode('crap4j'))
                        ->append($this->addOptionsNode('html'))
                        ->append($this->addOptionsNode('php'))
                        ->append($this->addOptionsNode('text'))
                        ->append($this->addOptionsNode('xml'))
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param string $name
     *
     * @return ArrayNodeDefinition
     */
    private function addOptionsNode($name)
    {
        $treeBuilder = new ArrayNodeDefinition($name);
        $normalizer  = function ($v) {
            return [
                'target' => $v,
            ];
        };

        return $treeBuilder
            ->beforeNormalization()
                ->ifString()->then($normalizer)
            ->end()
            ->scalarPrototype()->end();
    }

    private function configureFilterSection(ArrayNodeDefinition $builder)
    {
        $stringNormalizer = function ($v) {
            return ['directory' => $v];
        };

        $builder
            ->children()
                ->arrayNode('filter')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()->then($stringNormalizer)
                        ->end()
                        ->children()
                            ->scalarNode('directory')->defaultNull()->end()
                            ->scalarNode('file')->defaultNull()->end()
                            ->scalarNode('suffix')->defaultValue('.php')->end()
                            ->scalarNode('prefix')->defaultValue('')->end()
                            ->arrayNode('exclude')
                                ->arrayPrototype()
                                    ->beforeNormalization()
                                        ->ifString()->then($stringNormalizer)
                                    ->end()
                                    ->children()
                                        ->scalarNode('directory')->defaultNull()->end()
                                        ->scalarNode('file')->defaultNull()->end()
                                        ->scalarNode('suffix')->defaultNull()->end()
                                        ->scalarNode('prefix')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
