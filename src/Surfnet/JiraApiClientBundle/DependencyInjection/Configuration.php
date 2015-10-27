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

namespace Surfnet\JiraApiClientBundle\DependencyInjection;

use Surfnet\JiraApiClientBundle\Assert;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('surfnet_jira_api_client');

        $rootNode
            ->children()
                ->scalarNode('base_url')
                    ->info('The base URL of the JIRA API host')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($baseUrl) {
                            return !is_string($baseUrl);
                        })
                        ->thenInvalid('The JIRA API base URL should be a string')
                    ->end()
                    ->validate()
                        ->ifTrue(function ($baseUrl) {
                            return !filter_var($baseUrl, FILTER_VALIDATE_URL);
                        })
                        ->thenInvalid('The JIRA API base URL should be a valid URL')
                    ->end()
                    ->validate()
                        ->ifTrue(function ($baseUrl) {
                            return parse_url($baseUrl, PHP_URL_PATH) !== null;
                        })
                        ->thenInvalid('The JIRA API base URL should not contain a path')
                    ->end()
                ->end()
                ->booleanNode('verify_ssl')
                    ->info('Whether to verify the SSL certificate of the JIRA API')
                    ->defaultTrue()
                ->end()
                ->scalarNode('consumer_key')
                    ->info(
                        'The consumer key for the Application Link in JIRA'
                    )
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($consumerKey) {
                            return !is_string($consumerKey);
                        })
                        ->thenInvalid(
                            'The consumer key for the JIRA API Application Link private key must be a non-empty string'
                        )
                    ->end()
                ->end()
                ->scalarNode('private_key_file')
                    ->info(
                        'The path to the private key which public key was registered with the Application Link in JIRA'
                    )
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($path) {
                            return !is_string($path);
                        })
                        ->thenInvalid(
                            'The path to the JIRA API Application Link private key must be a non-empty string'
                        )
                    ->end()
                ->end()
                ->scalarNode('project_key')
                    ->info('The key of the project that will be reported to in JIRA')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($projectKey) {
                            return !is_string($projectKey);
                        })
                        ->thenInvalid('The project key should be a string')
                    ->end()
                ->end()
                ->scalarNode('default_assignee')
                    ->info('The name of the user that will be the default assignee')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($assignee) {
                            return !is_string($assignee);
                        })
                        ->thenInvalid('The default assignee should be a string')
                    ->end()
                ->end()
                ->scalarNode('default_reporter')
                    ->info('The name of the user that will be the default reporter')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($reporter) {
                            return !is_string($reporter);
                        })
                        ->thenInvalid('The default reporter should be a string')
                    ->end()
                ->end()
                ->scalarNode('issue_type')
                    ->info('The ID of the type of issue to create')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->always(function ($issueType) {
                            Assert::regex($issueType, '~^\d+$~', 'The issue type ID should be a string of digits');
                            return $issueType;
                        })
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
