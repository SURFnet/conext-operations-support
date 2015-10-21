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
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraIssue;
use Surfnet\Conext\OperationsSupportBundle\Reporter\JiraReporter;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraIssueService;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraReportService;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;

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
        JiraIssueStatus::configure(
            new JiraIssueStatus('10000'),
            new JiraIssueStatus('10002')
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

        /** @var MockInterface|VerificationSuiteResult $result */
        $result = m::mock(VerificationSuiteResult::class);
        $result->shouldReceive('hasTestFailed')->andReturn(true);
        $result->shouldReceive('getFailedTestName')->andReturn($testName);
        $result->shouldReceive('getReason')->andReturn($reason);
        $result->shouldReceive('getExplanation')->andReturn($explanation);
        $result->shouldReceive('getSeverity')->andReturn($severity);

        /** @var MockInterface|JiraReportService $reportService */
        $reportService = m::mock(JiraReportService::class);
        $reportService->shouldReceive('findMostRecentlyReported')->andReturn(null);

        /** @var MockInterface|JiraIssue $issue */
        $issue = m::mock(JiraIssue::class);

        /** @var MockInterface|JiraIssueService $issueService */
        $issueService = m::mock(JiraIssueService::class);
        $issueService
            ->shouldReceive('createIssue')
            ->once()
            ->with(
                self::voEquals(JiraIssueStatus::open()),
                self::voEquals(new JiraIssuePriority('10002')),
                $reason,
                self::containsAll((string) $entity, $explanation, $testName)
            )
            ->andReturn($issue);

        $reportId = m::mock(UuidInterface::class);
        /** @var MockInterface|UuidFactoryInterface $uuidFactory */
        $uuidFactory = m::mock(UuidFactoryInterface::class);
        $uuidFactory->shouldReceive('uuid4')->once()->with()->andReturn($reportId);
        $reportService
            ->shouldReceive('trackNewIssue')
            ->once()
            ->with($reportId, $issue, self::voEquals($entity), $testName);

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
}
