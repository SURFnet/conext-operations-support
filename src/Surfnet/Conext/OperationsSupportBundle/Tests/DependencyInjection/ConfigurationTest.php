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

namespace Surfnet\OperationsSupportBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\OperationsSupportBundle\Tests\DataProvider\DataProvider;
use Surfnet\Conext\OperationsSupportBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;
    use DataProvider;

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Configuration
     * @dataProvider notArrayProvider
     */
    public function suites_cannot_be_other_than_array($value)
    {
        $config = ['suites' => $value];
        $this->assertConfigurationIsInvalid([$config], 'Expected array');
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Configuration
     * @dataProvider notArrayProvider
     */
    public function suite_cannot_contain_other_than_array($value)
    {
        $config = [
            'suites' => [
                'suite_name' => $value
            ]
        ];

        $this->assertConfigurationIsInvalid([$config], 'Expected array');
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Configuration
     */
    public function can_contain_multiple_suites()
    {
        $config = [
            'suites' => [
                'suite_name' => [
                    'test1',
                    'test2'
                ],
                'suite_name_2' => [
                    'test1'
                ]
            ]
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'suites' => [
                'suite_name' => [
                    'test1',
                    'test2'
                ],
                'suite_name_2' => [
                    'test1'
                ]
            ]
        ] );
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Configuration
     */
    public function configurations_are_not_merged_deeply()
    {
        $configA = [
            'suites' => [
                'suite_name' => [
                    'test1',
                    'test2'
                ],
                'suite_name_2' => [
                    'test1'
                ]
            ]
        ];

        $configB = [
            'suites' => [
                'the_only_suite' => [
                    'the_only_test'
                ]
            ]
        ];

        $this->assertProcessedConfigurationEquals([$configA, $configB], $configB);
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
