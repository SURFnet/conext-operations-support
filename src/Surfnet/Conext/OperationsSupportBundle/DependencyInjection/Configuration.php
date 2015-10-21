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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Blacklist;
use Surfnet\Conext\EntityVerificationFramework\NameResolver;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('surfnet_conext_operations_support');

        $this->createSuitesConfiguration($rootNode);
        $this->createBlacklistConfiguration($rootNode);
        $this->createJiraConfiguration($rootNode);

        return $treeBuilder;
    }

    public function createSuitesConfiguration(ArrayNodeDefinition $rootNode)
    {
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
                ->end();
    }

    public function createBlacklistConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
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
                ->end();
    }

    public function createJiraConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('jira')
                    ->isRequired()
                    ->children()
                        ->scalarNode('open_status_id')
                            ->isRequired()
                            ->info(
                                'Sets the JIRA status ID that represents a open state. To determine, log into JIRA, ' .
                                'visit (/jira)/rest/api/2/status and find the status ID of the opened status (eg. ' .
                                'Open).'
                            )
                            ->validate()
                                ->always(function ($statusId) {
                                    new JiraIssueStatus($statusId);
                                    return $statusId;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('muted_status_id')
                            ->isRequired()
                            ->info(
                                'Sets the JIRA status ID that represents a muted state. To determine, log into JIRA, ' .
                                'visit (/jira)/rest/api/2/status and find the status ID of the muted status (eg. ' .
                                'On Hold).'
                            )
                            ->validate()
                                ->always(function ($statusId) {
                                    new JiraIssueStatus($statusId);
                                    return $statusId;
                                })
                            ->end()
                        ->end()
                        ->arrayNode('priority_severity_map')
                            ->isRequired()
                            ->info(
                                'Maps JIRA priority IDs to test failure severities ' .
                                '(critical, high, medium, low, trivial). Priority IDs must be determined using ' .
                                'JIRA\'s REST API. When logged into JIRA, visit (/jira)/rest/api/2/priority.'
                            )
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('priority_id')
                            ->prototype('scalar')->end()
                            ->beforeNormalization()
                                ->always(function (array $prioritySeverityMap) {
                                    return array_map(
                                        function ($severityName) {
                                            if (!is_string($severityName)) {
                                                return $severityName;
                                            }

                                            $constant = VerificationTestResult::class
                                                . '::SEVERITY_'
                                                . strtoupper($severityName);
                                            return defined($constant) ? constant($constant) : $severityName;
                                        },
                                        $prioritySeverityMap
                                    );
                                })
                            ->end()
                            ->validate()
                                ->always(function (array $prioritySeverityMap) {
                                    Assert::isArray($prioritySeverityMap, 'Priority severity map must be array');

                                    foreach ($prioritySeverityMap as $priorityId => $severity) {
                                        new JiraIssuePriority((string) $priorityId);
                                        Assert::inArray(
                                            $severity,
                                            VerificationTestResult::VALID_SEVERITIES,
                                            'Severity "%s" must be one of ' .
                                            '"trivial", "low", "medium", "high", "critical"'
                                        );
                                    }

                                    return $prioritySeverityMap;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
