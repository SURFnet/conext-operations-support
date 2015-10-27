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
use Surfnet\Conext\EntityVerificationFramework\NameResolver;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SurfnetConextOperationsSupportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->configureSuitesToRun($config, $container);
        $this->configureBlacklist($config, $container);
        $this->configureJira($config, $container);
    }

    private function configureSuitesToRun(array $config, ContainerBuilder $container)
    {
        $runner = $container->getDefinition('surfnet_conext_operations_support.verification_runner');

        foreach ($config['suites'] as $suiteName => $testNames) {
            $suiteClass = NameResolver::resolveToClass($suiteName);
            $suiteDefinition = new Definition($suiteClass);

            foreach ($testNames as $testName) {
                $testClass = NameResolver::resolveToClass($suiteName . '.' . $testName);
                $testDefinition = new Definition($testClass);

                $suiteDefinition->addMethodCall('addVerificationTest', [$testDefinition]);
            }

            $runner->addMethodCall('addVerificationSuite', [$suiteDefinition]);
        }
    }

    private function configureBlacklist(array $config, ContainerBuilder $container)
    {
        $blacklist = $container->getDefinition('surfnet_conext_operations_support.verification_blacklist');
        $blacklist->replaceArgument(0, $config['blacklist']);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureJira(array $config, ContainerBuilder $container)
    {
        $container
            ->getDefinition('surfnet_conext_operations_support.service.jira_issue')
            ->replaceArgument(1, [
                JiraIssueStatus::OPEN   => $config['jira']['status_mapping']['open'],
                JiraIssueStatus::MUTED  => $config['jira']['status_mapping']['muted'],
                JiraIssueStatus::CLOSED => $config['jira']['status_mapping']['closed'],
            ])
            ->replaceArgument(2, [
                VerificationTestResult::SEVERITY_TRIVIAL  => $config['jira']['priority_mapping']['trivial'],
                VerificationTestResult::SEVERITY_LOW      => $config['jira']['priority_mapping']['low'],
                VerificationTestResult::SEVERITY_MEDIUM   => $config['jira']['priority_mapping']['medium'],
                VerificationTestResult::SEVERITY_HIGH     => $config['jira']['priority_mapping']['high'],
                VerificationTestResult::SEVERITY_CRITICAL => $config['jira']['priority_mapping']['critical'],
            ]);
    }
}
