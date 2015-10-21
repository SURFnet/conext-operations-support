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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Reporter;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Reporter\JiraReporter;
use Surfnet\Conext\OperationsSupportBundle\Repository\JiraReportRepository;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Result\CreateIssueResult;
use Surfnet\JiraApiClientBundle\Service\IssueService;

class JiraReporterTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        JiraIssuePriority::configure(
            [
                '10000' => VerificationTestResult::SEVERITY_TRIVIAL,
                '10001' => VerificationTestResult::SEVERITY_LOW,
                '10002' => VerificationTestResult::SEVERITY_MEDIUM,
                '10003' => VerificationTestResult::SEVERITY_HIGH,
                '10004' => VerificationTestResult::SEVERITY_CRITICAL,
            ],
            '10002'
        );
    }

    /**
     * @test
     * @group reporter
     */
    public function it_tracks_new_issues_using_a_new_report()
    {
        $entityId   = new EntityId('meh');
        $entityType = EntityType::IdP();
        $entity     = new Entity($entityId, $entityType);

        $testName = 'test.name';
        $reason = 'reason';
        $explanation = 'explanation';
        $severity = VerificationTestResult::SEVERITY_MEDIUM;

        /** @var MockInterface|JiraReportRepository $reportRepository */
        $reportRepository = m::mock(JiraReportRepository::class);
        $reportRepository->shouldReceive('findMostRecentlyReported')->andReturn(null);
        $reportRepository->shouldReceive('add')->once()->with(m::type(JiraReport::class));

        $command = new CreateIssueCommand();
        $command->priority = '10002';
        $command->summary = $reason;
        $command->description = $explanation;
        $result = CreateIssueResult::success('CONOPS-119', '10000');

        /** @var MockInterface|IssueService $issueService */
        $issueService = m::mock(IssueService::class);
        $issueService->shouldReceive('createIssue')->once()->with(m::anyOf($command))->andReturn($result);

        $reporter = new JiraReporter($reportRepository, $issueService, new NullLogger());

        /** @var MockInterface|VerificationSuiteResult $result */
        $result = m::mock(VerificationSuiteResult::class);
        $result->shouldReceive('hasTestFailed')->andReturn(true);
        $result->shouldReceive('getFailedTestName')->andReturn($testName);
        $result->shouldReceive('getReason')->andReturn($reason);
        $result->shouldReceive('getExplanation')->andReturn($explanation);
        $result->shouldReceive('getSeverity')->andReturn($severity);

        $reporter->reportFailedVerificationFor($entity, $result);

    }
}
