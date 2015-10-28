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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Service;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraIssueService;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Service\IssueService;

/**
 * @runTestsInSeparateProcesses
 */
class JiraIssueServiceTest extends TestCase
{
    /**
     * @test
     * @group service
     * @group jira
     */
    public function it_successfully_creates_issues()
    {
        $priority    = new JiraIssuePriority('10002');
        $summary     = 'summary';
        $description = 'description';

        $command = new CreateIssueCommand();
        $command->priorityId  = $priority->getPriorityId();
        $command->summary     = $summary;
        $command->description = $description;

        /** @var MockInterface|IssueService $issueApiService */
        $issueApiService = m::mock(IssueService::class);
        $issueApiService->shouldReceive('createIssue')->once()->with(m::anyOf($command))->andReturn('CONOPS-10');

        $service = new JiraIssueService($issueApiService, [], [], new NullLogger());
        $issueKey = $service->createIssue($priority, $summary, $description);

        $this->assertSame('CONOPS-10', $issueKey);
    }

    /**
     * @test
     * @group service
     * @group jira
     */
    public function it_maps_status_to_jira_status_id()
    {
        /** @var MockInterface|IssueService $issueApiService */
        $issueApiService = m::mock(IssueService::class);
        $service = new JiraIssueService(
            $issueApiService,
            [JiraIssueStatus::OPEN => '10000', JiraIssueStatus::MUTED => '10001', JiraIssueStatus::CLOSED => '10002'],
            [],
            new NullLogger()
        );

        $this->assertEquals(new JiraIssueStatus('10000'), $service->mapStatusToJiraStatusId(JiraIssueStatus::OPEN));
        $this->assertEquals(new JiraIssueStatus('10002'), $service->mapStatusToJiraStatusId(JiraIssueStatus::CLOSED));
    }

    /**
     * @test
     * @group service
     * @group jira
     */
    public function it_maps_severity_to_jira_priority_id()
    {
        /** @var MockInterface|IssueService $issueApiService */
        $issueApiService = m::mock(IssueService::class);
        $service = new JiraIssueService(
            $issueApiService,
            [],
            [VerificationTestResult::SEVERITY_TRIVIAL => '10000', VerificationTestResult::SEVERITY_LOW => '10001'],
            new NullLogger()
        );

        $this->assertEquals(
            new JiraIssuePriority('10000'),
            $service->mapSeverityToJiraPriorityId(VerificationTestResult::SEVERITY_TRIVIAL)
        );
        $this->assertEquals(
            new JiraIssuePriority('10001'),
            $service->mapSeverityToJiraPriorityId(VerificationTestResult::SEVERITY_LOW)
        );
    }
}
