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

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\DateTime\DateTime;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraIssue;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;

class JiraReportTest extends TestCase
{
    /**
     * @test
     * @group jira
     */
    public function an_issue_can_be_tracked()
    {
        JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', $this->issue());
    }

    /**
     * @test
     * @group jira
     */
    public function can_indicate_issue_needs_updating()
    {
        $priority = new JiraIssuePriority('1000');
        $oldSummary = 'old_summary';
        $newSummary = 'new_summary';
        $description = 'description';

        /** @var JiraIssue|MockInterface $issue0 */
        $issue0 = m::mock(JiraIssue::class);
        $issue0->shouldReceive('needsUpdating')->with($priority, $oldSummary, $description)->andReturn(false);
        $issue0->shouldReceive('needsUpdating')->with($priority, $newSummary, $description)->andReturn(true);

        $report = JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', $issue0);
        $this->assertFalse(
            $report->issueNeedsUpdating($priority, $oldSummary, $description),
            "Report states issue needs updating, even though it's exactly the same"
        );
        $this->assertTrue(
            $report->issueNeedsUpdating($priority, $newSummary, $description),
            "Report states issue doesn't need updating, even though it's different"
        );
    }

    /**
     * @test
     * @group jira
     */
    public function issue_can_be_updated()
    {
        /** @var JiraIssue|MockInterface $issue0 */
        $issue0 = m::mock(JiraIssue::class);
        /** @var JiraIssue|MockInterface $issue1 */
        $issue1 = m::mock(JiraIssue::class);

        $report = JiraReport::trackIssue($this->uuid(), $this->entity(), 'test.name', $issue0);
        $report->issueUpdated($issue1);
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

    /**
     * @return JiraIssue|MockInterface
     */
    private function issue()
    {
        return m::mock(JiraIssue::class);
    }
}
