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

class BlacklistConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    const SUITE_ONE = 'one_suite';
    const SUITE_TWO = 'two_suite';
    const SUITE_ONE_TEST_ONE = 'one_suite.one_test';

    /**
     * @test
     * @group Configuration
     */
    public function accepts_valid_configuration()
    {
        $configuration = [
            'blacklist' => [
                '*' => [
                    ['https://sp.invalid', 'sp'],
                    ['https://idp.invalid', 'idp'],
                ],
                self::SUITE_ONE => [],
                self::SUITE_ONE_TEST_ONE => [
                    ['https://meh.invalid', 'idp'],
                ],
            ]
        ];

        $expectedProcessedConfiguration = [
            'blacklist' => [
                '*' => [
                    ['https://sp.invalid', 'sp'],
                    ['https://idp.invalid', 'idp'],
                ],
                self::SUITE_ONE => [],
                self::SUITE_ONE_TEST_ONE => [
                    ['https://meh.invalid', 'idp'],
                ],
            ]
        ];

        $this->assertProcessedConfigurationEquals([$configuration], $expectedProcessedConfiguration, 'blacklist');
    }

    /**
     * @test
     * @group Configuration
     */
    public function entity_types_must_be_valid()
    {
        $configuration = [
            'blacklist' => [
                '*' => [
                    ['https://sp.invalid', 'meh'],
                ],
            ]
        ];

        $this->assertPartialConfigurationIsInvalid(
            [$configuration],
            'blacklist',
            'Value "meh" is not an element of the valid values: sp, idp'
        );
    }

    /**
     * @test
     * @group Configuration
     */
    public function entity_ids_must_be_valid()
    {
        $configuration = [
            'blacklist' => [
                '*' => [
                    ['', 'sp'],
                ],
            ]
        ];

        $this->assertPartialConfigurationIsInvalid(
            [$configuration],
            'blacklist',
            'Value "" is empty, but non empty value was expected.'
        );
    }

    /**
     * @test
     * @group Configuration
     * @dataProvider nonExistentSuiteOrTestNames
     *
     * @param string $nonExistentSuiteOrTestName
     */
    public function suites_and_tests_names_must_resolve_to_an_existing_class($nonExistentSuiteOrTestName)
    {
        $configuration = [
            'blacklist' => [
                $nonExistentSuiteOrTestName => [
                    ['entity', 'sp'],
                ],
            ]
        ];

        $this->assertPartialConfigurationIsInvalid(
            [$configuration],
            'blacklist',
            '~Resolved class "[^"]+" does not exist$~',
            true
        );
    }

    public function nonExistentSuiteOrTestNames()
    {
        return [
            'invalid.suite.or.test.name' => ['invalid.suite.or.test.name'],
            'nonexistent.test' => ['nonexistent.test'],
            'nonexistentsuite' => ['nonexistentsuite'],
        ];
    }

    /**
     * @test
     * @group Configuration
     * @dataProvider notArraysOrNulls
     */
    public function suites_and_tests_must_contain_an_array($nonArray)
    {
        $configuration = [
            'blacklist' => [
                self::SUITE_TWO => $nonArray,
            ]
        ];

        $this->assertPartialConfigurationIsInvalid(
            [$configuration],
            'blacklist',
            'Expected array, but got '
        );
    }

    public function notArraysOrNulls()
    {
        return [
            'empty string' => [''],
            'blank string' => [' '],
            'string'       => ['string'],
            'int'          => [1],
            'int 0'        => [0],
            'float'        => [1.23],
            'float 0'      => [0.0],
            'bool'         => [false],
            'object'       => [new \stdClass],
            'resource'     => [fopen('php://memory', 'w')],
        ];
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
