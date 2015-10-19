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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Entity;

use DateInterval;
use DateTimeImmutable;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\DateTime\DateTime;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;

class JiraReportTest extends TestCase
{
    protected function tearDown()
    {
        DateTime::stopMockingNow();
    }

    /**
     * @test
     * @group jira
     */
    public function an_issue_can_be_tracked()
    {
        JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', 'CONOPS-1');
    }

    /**
     * @test
     * @group jira
     */
    public function a_report_is_not_muted_by_default()
    {
        $report = JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', 'CONOPS-1');

        $this->assertFalse(
            $report->isMuted(),
            "A JIRA report is muted by default, while it shouldn't be"
        );
        $this->assertFalse(
            $report->hasBeenMutedForMoreThan(new DateInterval('PT0S')),
            "A JIRA report is muted for longer than 0 seconds, while it shouldn't be"
        );
    }

    /**
     * @test
     * @group jira
     */
    public function a_report_can_be_muted()
    {
        DateTime::mockNow(new DateTimeImmutable('1988-08-16 06:28:02'));

        $report = JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', 'CONOPS-1');
        $report->mute();

        DateTime::mockNow(new DateTimeImmutable('2020-01-01 00:00:00'));

        $this->assertTrue(
            $report->isMuted(),
            "A JIRA report is muted by default, while it shouldn't be"
        );
        $this->assertTrue(
            $report->hasBeenMutedForMoreThan(new DateInterval('P30Y')),
            "A JIRA report has been muted for more than 30 years, but report reports otherwise"
        );
        $this->assertFalse(
            $report->hasBeenMutedForMoreThan(new DateInterval('P35Y')),
            "A JIRA report has been muted for less than 35 years, but report reports otherwise"
        );
    }

    /**
     * @test
     * @group jira
     */
    public function a_report_can_be_tracked_using_a_new_issue()
    {
        $report0 = JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', 'CONOPS-1');
        $report1 = $report0->trackUsingNewIssue($this->uuid(), 'CONOPS-2');

        $this->assertNotSame(
            $report0,
            $report1,
            'A report for tracking a new issue should not be the same as the previous report'
        );
    }

    /**
     * @return UuidInterface
     */
    private function uuid()
    {
        return Uuid::uuid4();
    }

    /**
     * @return Entity
     */
    private function entity()
    {
        return new Entity(new EntityId('meh'), EntityType::IdP());
    }
}
