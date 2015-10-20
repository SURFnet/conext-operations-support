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
    const DEFAULT_PRIORITY_ID = '1';

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
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage Default priority ID must consist of one or more digits
     */
    public function default_priority_id_must_be_valid()
    {
        JiraIssuePriority::configure(self::PRIORITY_SEVERITY_MAP, 'abc');
    }

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage Given default priority ID doesn't map to a severity
     */
    public function default_priority_id_must_be_one_of_the_configured_priority_ids()
    {
        JiraIssuePriority::configure(self::PRIORITY_SEVERITY_MAP, '1988');
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
    public function the_default_priority_can_be_requested()
    {
        $this->configurePriorities();

        $this->assertTrue(
            JiraIssuePriority::forDefaultPriority()->equals(JiraIssuePriority::forPriority(self::DEFAULT_PRIORITY_ID)),
            sprintf(
                'Default JiraIssuePriority should be equal to JiraIssuePriority for priority ID "%s"',
                self::DEFAULT_PRIORITY_ID
            )
        );
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

    private function configurePriorities()
    {
        JiraIssuePriority::configure(self::PRIORITY_SEVERITY_MAP, self::DEFAULT_PRIORITY_ID);
    }
}
