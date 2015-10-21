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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;

/**
 * @runTestsInSeparateProcesses
 */
class JiraIssuePriorityTest extends TestCase
{
    const PRIORITY_SEVERITY_MAP = [
        '10000' => VerificationTestResult::SEVERITY_TRIVIAL,
        '10046' => VerificationTestResult::SEVERITY_LOW,
        '1'     => VerificationTestResult::SEVERITY_MEDIUM,
        '1002'  => VerificationTestResult::SEVERITY_HIGH,
        '10001' => VerificationTestResult::SEVERITY_CRITICAL,
    ];

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\OperationsSupportBundle\Exception\LogicException
     * @expectedExceptionMessage JiraIssuePriority has not yet been configured
     */
    public function priorities_must_be_configured()
    {
        JiraIssuePriority::forPriority('10000');
    }

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException
     * @expectedExceptionMessage All test failure severities must be mapped to a JIRA priority
     */
    public function priority_ids_must_mapped_for_all_severities()
    {
        JiraIssuePriority::configure([], '10000');
    }

    /**
     * @test
     * @group value
     * @dataProvider arrayKeysNotSuitableAsPriorityIds
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage Priority IDs must consist of one or more digits
     *
     * @param mixed $nonDigitString
     */
    public function priority_ids_must_be_valid($priorityId)
    {
        JiraIssuePriority::configure(
            [
                '10000' => VerificationTestResult::SEVERITY_TRIVIAL,
                $priorityId => VerificationTestResult::SEVERITY_LOW,
                '3931'     => VerificationTestResult::SEVERITY_MEDIUM,
                '1002'  => VerificationTestResult::SEVERITY_HIGH,
                '10001' => VerificationTestResult::SEVERITY_CRITICAL,
            ],
            '10000'
        );
    }

    /**
     * @test
     * @group value
     * @dataProvider nonSeverities
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException
     * @expectedExceptionMessage All test failure severities must be mapped to a JIRA priority
     *
     * @param mixed $nonSeverity
     */
    public function severities_must_be_all_valid_severities($nonSeverity)
    {
        JiraIssuePriority::configure(
            [
                '10000' => VerificationTestResult::SEVERITY_TRIVIAL,
                '1984'  => $nonSeverity,
                '1'     => VerificationTestResult::SEVERITY_MEDIUM,
                '1002'  => VerificationTestResult::SEVERITY_HIGH,
                '10001' => VerificationTestResult::SEVERITY_CRITICAL,
            ],
            '10000'
        );
    }

    /**
     * @test
     * @group value
     */
    public function one_can_be_created_from_a_priority_id()
    {
        $this->configurePriorities();

        JiraIssuePriority::forPriority('10000');
    }

    /**
     * @test
     * @group value
     */
    public function one_can_check_a_priority_severity_mapping_exists()
    {
        $this->configurePriorities();

        $this->assertTrue(
            JiraIssuePriority::hasMappingToSeverity('10000'),
            'JiraIssuePriority should have mapping for priority ID "10000"'
        );
        $this->assertFalse(
            JiraIssuePriority::hasMappingToSeverity('1988'),
            'JiraIssuePriority should not have mapping for priority ID "1988"'
        );
    }

    /**
     * @test
     * @group value
     */
    public function one_can_be_created_for_a_severity()
    {
        $this->configurePriorities();

        JiraIssuePriority::forSeverity(VerificationTestResult::SEVERITY_TRIVIAL);
    }

    /**
     * @test
     * @group value
     */
    public function priorities_can_be_compared_for_equality()
    {
        $this->configurePriorities();

        $priorityTrivial = JiraIssuePriority::forSeverity(VerificationTestResult::SEVERITY_TRIVIAL);
        $priority10000 = JiraIssuePriority::forPriority('10000');
        $priority10046 = JiraIssuePriority::forPriority('10046');

        $this->assertTrue(
            $priorityTrivial->equals($priority10000),
            'Priority of severity trivial should  equal priority for ID "10000"'
        );
        $this->assertFalse(
            $priorityTrivial->equals($priority10046),
            'Priority of severity trivial should not equal priority for ID "10046"'
        );
    }

    /**
     * @test
     * @group value
     */
    public function an_unmapped_priority_can_be_created()
    {
        new JiraIssuePriority('10038');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonDigitStrings
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage Priority ID must consist of one or more digits
     *
     * @param mixed $nonDigitString
     */
    public function an_unmapped_priority_doesnt_accept_an_invalid_priority_id($nonDigitString)
    {
        new JiraIssuePriority($nonDigitString);
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
            'array'    => [[]],
            'object'   => [new \stdClass],
            'resource' => [fopen('php://memory', 'w')],
        ];
    }

    public function arrayKeysNotSuitableAsPriorityIds()
    {
        return [
            'empty'    => [''],
            'alphanum' => ['192abc'],
        ];
    }

    public function nonSeverities()
    {
        return [
            'empty'    => [''],
            'alphanum' => ['192abc'],
            'int'      => [18383],
            'float'    => [38.23],
            'null'     => [null],
            'bool'     => [false],
        ];
    }

    private function configurePriorities()
    {
        JiraIssuePriority::configure(self::PRIORITY_SEVERITY_MAP);
    }
}
