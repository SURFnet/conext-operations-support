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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\OperationsSupportBundle\DependencyInjection\Configuration;

class JiraConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    private $validConfiguration = [
        'jira' => [
            'status_mapping' => [
                'open'     => '10000',
                'muted'    => '10001',
                'closed'   => '10002',
            ],
            'priority_mapping' => [
                 'trivial'  => '10000',
                 'low'      => '10001',
                 'medium'   => '10002',
                 'high'     => '10003',
                 'critical' => '10004',
            ],
        ]
    ];

    /**
     * @test
     * @group Configuration
     */
    public function it_accepts_a_valid_configuration()
    {
        $this->assertConfigurationIsValid(['surfnet_conext_operations_support' => $this->validConfiguration], 'jira');
    }

    /**
     * @test
     * @group Configuration
     * @dataProvider nonDigitStrings
     *
     * @param nonDigitString
     */
    public function status_ids_are_validated($nonDigitString)
    {
        $config = $this->validConfiguration;
        $config['jira']['status_mapping']['open'] = $nonDigitString;

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            'Invalid configuration for path "surfnet_conext_operations_support.jira.status_mapping.open": ' .
            'JIRA issue status ID must consist of one or more digits'
        );
    }

    /**
     * @test
     * @group Configuration
     * @dataProvider nonDigitStrings
     *
     * @param mixed $nonDigitString
     */
    public function priority_ids_in_priority_mapping_are_validated($nonDigitString)
    {
        $config = $this->validConfiguration;
        $config['jira']['priority_mapping']['trivial'] = $nonDigitString;

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            'Invalid configuration for path "surfnet_conext_operations_support.jira.priority_mapping.trivial": ' .
            'Priority ID must consist of one or more digits, got '
        );
    }

    public function nonDigitStrings()
    {
        return [
            'empty'    => [''],
            'alphanum' => ['192abc'],
            'int'      => [1],
            'int 0'    => [0],
            'float'    => [1.23],
            'float 0'  => [0.0],
            'null'     => [null],
            'bool'     => [false],
        ];
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
