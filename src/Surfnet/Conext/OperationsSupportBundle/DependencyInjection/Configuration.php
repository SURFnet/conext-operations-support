<?php

/**
 * Copyright 2015 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\Conext\OperationsSupportBundle\DependencyInjection;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Blacklist;
use Surfnet\Conext\EntityVerificationFramework\NameResolver;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('surfnet_conext_operations_support');

        $rootNode
            ->children()
                ->arrayNode('suites')
                    ->info('The test suites that should be run')
                    ->isRequired()
                    ->performNoDeepMerging()
                    ->prototype('array')
                        ->info('The tests within the suites that should be run')
                        ->prototype('scalar')
                            ->info('The name of the test that should be run')
                            ->validate()
                                ->ifTrue(function ($test) {
                                    return !is_string($test);
                                })
                                ->thenInvalid('Test name should be a string')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('blacklist')
                    ->info('Blacklist tests or entire suites for specific entities (suite, suite.test, *)')
                    ->performNoDeepMerging()
                    ->validate()
                        ->always(function ($suitesOrTests) {
                            foreach (array_keys($suitesOrTests) as $suiteOrTestName) {
                                if ($suiteOrTestName === Blacklist::WILDCARD) {
                                    continue;
                                }

                                NameResolver::resolveToClass($suiteOrTestName);
                            }

                            return $suitesOrTests;
                        })
                    ->end()
                    ->prototype('array')
                        ->info('The entities to blacklist for this test, suite, or wildcard')
                        ->prototype('variable')
                            ->info('The entity to blacklist for this test, suite, or wildcard')
                            ->validate()
                                ->always(function ($entity) {
                                    Assert::count($entity, 2);
                                    new EntityId($entity[0]);
                                    Assert::inArray($entity[1], ['sp', 'idp']);
                                    return $entity;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
