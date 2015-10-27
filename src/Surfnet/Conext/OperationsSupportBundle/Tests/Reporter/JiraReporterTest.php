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
use Mockery\Matcher\Closure as ClosureMatcher;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Reporter\JiraReporter;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraIssueService;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraReportService;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssue;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;

class JiraReporterTest extends TestCase
{
    const STATUS_ID_OPEN = '10000';
    const STATUS_ID_MUTED = '10001';
    const PRIORITY_ID_MEDIUM = '10002';

    const FAILED_TEST_NAME = 'test.name';
    const FAILED_TEST_REASON = 'reason';
    const FAILED_TEST_EXPLANATION = 'explanation';
    const FAILED_TEST_SEVERITY = VerificationTestResult::SEVERITY_MEDIUM;

    /**
     * @test
     * @group reporter
     */
    public function it_tracks_new_issues_using_a_new_report()
    {
        $entityId   = new EntityId('meh');
        $entityType = EntityType::IdP();
        $entity     = new Entity($entityId, $entityType);

        $result = $this->getFailedSuiteResult();

        /** @var MockInterface|JiraReportService $reportService */
        $reportService = m::mock(JiraReportService::class);
        $reportService->shouldReceive('findMostRecentlyReported')->andReturn(null);

        $issueKey = 'CONOPS-13';

        /** @var MockInterface|JiraIssueService $issueService */
        $issueService = m::mock(JiraIssueService::class);
        $issueService
            ->shouldReceive('createIssue')
            ->once()
            ->with(
                self::voEquals(new JiraIssuePriority(self::PRIORITY_ID_MEDIUM)),
                self::FAILED_TEST_REASON,
                self::containsAll((string) $entity, self::FAILED_TEST_EXPLANATION, self::FAILED_TEST_NAME)
            )
            ->andReturn($issueKey);
        $issueService
            ->shouldReceive('mapSeverityToJiraPriorityId')
            ->with(self::FAILED_TEST_SEVERITY)
            ->andReturn(new JiraIssuePriority(self::PRIORITY_ID_MEDIUM));

        $reportId = m::mock(UuidInterface::class);
        /** @var MockInterface|UuidFactoryInterface $uuidFactory */
        $uuidFactory = m::mock(UuidFactoryInterface::class);
        $uuidFactory->shouldReceive('uuid4')->once()->with()->andReturn($reportId);
        $reportService
            ->shouldReceive('trackNewIssue')
            ->once()
            ->with($reportId, $issueKey, self::voEquals($entity), self::FAILED_TEST_NAME);

        $reporter = new JiraReporter($reportService, $issueService, $uuidFactory, new NullLogger());
        $reporter->reportFailedVerificationFor($entity, $result);
    }

    /**
     * @test
     * @group reporter
     */
    public function it_ignores_muted_issues()
    {
        $entityId   = new EntityId('meh');
        $entityType = EntityType::IdP();
        $entity     = new Entity($entityId, $entityType);

        $result = $this->getFailedSuiteResult();

        $issueKey = 'CONOPS-13';
        $reportId = Uuid::uuid4();
        $report   = JiraReport::trackIssue($reportId, $issueKey, $entity, self::FAILED_TEST_NAME);
        $issue    = new JiraIssue(
            new JiraIssuePriority(self::PRIORITY_ID_MEDIUM),
            new JiraIssueStatus(self::STATUS_ID_MUTED),
            'summary',
            'description'
        );

        /** @var MockInterface|JiraReportService $reportService */
        $reportService = m::mock(JiraReportService::class);
        $reportService->shouldReceive('findMostRecentlyReported')->andReturn($report);

        /** @var MockInterface|JiraIssueService $issueService */
        $issueService = m::mock(JiraIssueService::class);
        $issueService->shouldReceive('getIssue')->once()->with($issueKey)->andReturn($issue);
        $issueService
            ->shouldReceive('mapSeverityToJiraPriorityId')
            ->with(self::FAILED_TEST_SEVERITY)
            ->andReturn(new JiraIssuePriority(self::PRIORITY_ID_MEDIUM));
        $issueService
            ->shouldReceive('mapStatusToJiraStatusId')
            ->with(JiraIssueStatus::MUTED)
            ->andReturn(new JiraIssueStatus(self::STATUS_ID_MUTED));

        /** @var MockInterface|UuidFactoryInterface $uuidFactory */
        $uuidFactory = m::mock(UuidFactoryInterface::class);

        $reporter = new JiraReporter($reportService, $issueService, $uuidFactory, new NullLogger());
        $reporter->reportFailedVerificationFor($entity, $result);
    }

    /**
     * @param object $expectedValueObject
     * @return callable
     */
    private static function voEquals($expectedValueObject)
    {
        return m::on(
            function ($actualValueObject) use ($expectedValueObject) {
                return $actualValueObject->equals($expectedValueObject);
            }
        );
    }

    /**
     * @param string[] ...$expecteds
     * @return ClosureMatcher
     */
    private static function containsAll(...$expecteds)
    {
        return m::on(function ($actual) use ($expecteds) {
            foreach ($expecteds as $expected) {
                if (strpos($actual, $expected) === false) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @return MockInterface|VerificationSuiteResult
     */
    private function getFailedSuiteResult()
    {
        /** @var MockInterface|VerificationSuiteResult $result */
        $result = m::mock(VerificationSuiteResult::class);
        $result->shouldReceive('hasTestFailed')->andReturn(true);
        $result->shouldReceive('getFailedTestName')->andReturn(self::FAILED_TEST_NAME);
        $result->shouldReceive('getReason')->andReturn(self::FAILED_TEST_REASON);
        $result->shouldReceive('getExplanation')->andReturn(self::FAILED_TEST_EXPLANATION);
        $result->shouldReceive('getSeverity')->andReturn(self::FAILED_TEST_SEVERITY);

        return $result;
    }
}
