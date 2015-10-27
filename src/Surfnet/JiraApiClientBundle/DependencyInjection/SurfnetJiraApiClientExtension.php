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

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SurfnetJiraApiClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container
            ->getDefinition('surfnet_jira_api_client.guzzle')
            ->replaceArgument(0, [
                'handler' => new Reference('surfnet_jira_api_client.guzzle.handler_stack'),
                'auth'    => 'oauth',
                'verify'  => $config['verify_ssl'],
            ]);

        $container
            ->getDefinition('surfnet_jira_api_client.jira_client')
            ->replaceArgument(0, $config['base_url']);

        $container
            ->getDefinition('surfnet_jira_api_client.guzzle.subscriber.oauth')
            ->replaceArgument(0, [
                'token'                  => '',
                'consumer_key'           => $config['consumer_key'],
                'private_key_file'       => $config['private_key_file'],
                'private_key_passphrase' => '',
                'signature_method'       => Oauth1::SIGNATURE_METHOD_RSA,
            ]);

        $container
            ->getDefinition('surfnet_jira_api_client.service.rest_issue')
            ->replaceArgument(1, $config['project_key'])
            ->replaceArgument(2, $config['issue_type']);
    }
}
