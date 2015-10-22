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
use Surfnet\JiraApiClientBundle\Result\CreateIssueResult;
use Surfnet\JiraApiClientBundle\Service\IssueService;

/**
 * @runTestsInSeparateProcesses
 */
class JiraIssueServiceTest extends TestCase
{
    public function setUp()
    {
        JiraIssuePriority::configure(
            [
                '10000' => VerificationTestResult::SEVERITY_TRIVIAL,
                '10001' => VerificationTestResult::SEVERITY_LOW,
                '10002' => VerificationTestResult::SEVERITY_MEDIUM,
                '10003' => VerificationTestResult::SEVERITY_HIGH,
                '10004' => VerificationTestResult::SEVERITY_CRITICAL,
            ]
        );
        JiraIssueStatus::configure(
            new JiraIssueStatus('10000'),
            new JiraIssueStatus('10002')
        );
    }

    /**
     * @test
     * @group service
     * @group jira
     */
    public function it_successfully_creates_issues()
    {
        $status      = JiraIssueStatus::open();
        $priority    = JiraIssuePriority::forSeverity(VerificationTestResult::SEVERITY_MEDIUM);
        $summary     = 'summary';
        $description = 'description';

        $command = new CreateIssueCommand();
        $command->statusId    = $status->getStatusId();
        $command->priorityId  = $priority->getPriorityId();
        $command->summary     = $summary;
        $command->description = $description;

        $result = CreateIssueResult::success('CONOPS-10');

        /** @var MockInterface|IssueService $issueApiService */
        $issueApiService = m::mock(IssueService::class);
        $issueApiService->shouldReceive('createIssue')->once()->with(m::anyOf($command))->andReturn($result);

        $service = new JiraIssueService($issueApiService, new NullLogger());
        $issueId = $service->createIssue($status, $priority, $summary, $description);

        $this->assertSame('CONOPS-10', $issueId);
    }

    /**
     * @test
     * @group service
     * @group jira
     * @expectedException \Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException
     * @expectedExceptionMessageRegExp ~JIRA issue creation unexpectedly failed due to API client error.+I.M. Weasel.+I.R. Baboon~
     */
    public function it_throws_on_client_errors()
    {
        $status      = JiraIssueStatus::open();
        $priority    = JiraIssuePriority::forSeverity(VerificationTestResult::SEVERITY_MEDIUM);
        $summary     = 'summary';
        $description = 'description';

        $command = new CreateIssueCommand();
        $command->statusId    = $status->getStatusId();
        $command->priorityId  = $priority->getPriorityId();
        $command->summary     = $summary;
        $command->description = $description;

        $result = CreateIssueResult::clientError(['I.M. Weasel', 'I.R. Baboon']);

        /** @var MockInterface|IssueService $issueApiService */
        $issueApiService = m::mock(IssueService::class);
        $issueApiService->shouldReceive('createIssue')->once()->with(m::anyOf($command))->andReturn($result);

        $service = new JiraIssueService($issueApiService, new NullLogger());
        $service->createIssue($status, $priority, $summary, $description);
    }
}
