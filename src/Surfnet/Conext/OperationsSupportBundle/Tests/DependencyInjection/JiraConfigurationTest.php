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
            'open_status_id' => '10000',
            'muted_status_id' => '10002',
            'priority_severity_map' => [
                '10000' => 'trivial',
                '10001' => 'low',
                '10002' => 'medium',
                '10003' => 'high',
                '10004' => 'critical',
            ],
        ]
    ];

    /**
     * @test
     * @group Configuration
     * @dataProvider nonDigitStrings
     *
     * @param nonDigitString
     */
    public function open_status_id_is_validated($nonDigitString)
    {
        $config = $this->validConfiguration;
        $config['jira']['open_status_id'] = $nonDigitString;

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            '~open_status_id.+JIRA issue status ID must consist of one or more digits~',
            true
        );
    }

    /**
     * @test
     * @group Configuration
     * @dataProvider nonDigitStrings
     *
     * @param nonDigitString
     */
    public function muted_status_id_is_validated($nonDigitString)
    {
        $config = $this->validConfiguration;
        $config['jira']['muted_status_id'] = $nonDigitString;

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            '~muted_status_id.+JIRA issue status ID must consist of one or more digits~',
            true
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

    /**
     * @test
     * @group Configuration
     * @dataProvider arrayKeysNotSuitableAsPriorityIds
     *
     * @param mixed $priorityId
     */
    public function priority_ids_in_priority_severity_map_are_validated($priorityId)
    {
        $config = $this->validConfiguration;
        $config['jira']['priority_severity_map']['192abc'] = 'medium';

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            '~priority_severity_map.+Priority ID must consist of one or more digits~',
            true
        );
    }

    public function arrayKeysNotSuitableAsPriorityIds()
    {
        return [
            'empty'    => [''],
            'alphanum' => ['192abc'],
        ];
    }
    
    /**
     * @test
     * @group Configuration
     * @dataProvider invalidSeverityNames
     */
    public function severities_are_validated($invalidSeverityName)
    {
        $config = $this->validConfiguration;
        $config['jira']['priority_severity_map']['10000'] = $invalidSeverityName;

        $this->assertPartialConfigurationIsInvalid(
            [$config],
            'jira',
            '~priority_severity_map.+(Severity.+must be one of|Expected scalar, but got)~',
            true
        );
    }

    public function invalidSeverityNames()
    {
        return [
            'unknown severity name' => ['worse'],
            'integer'  => [8],
            'int 0'    => [0],
            'float'    => [1.23],
            'float 0'  => [0.0],
            'null'     => [null],
            'bool'     => [false],
            'array'    => [[]],
            'object'   => [new \stdClass],
            'resource' => [fopen('php://memory', 'w')],
        ];
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
