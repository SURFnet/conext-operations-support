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
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Repository\JiraReportRepository;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraReportService;

class JiraReportServiceTest extends TestCase
{
    /**
     * @test
     * @group service
     */
    public function it_tracks_new_issues_using_a_new_report()
    {
        /** @var MockInterface|UuidInterface $reportId */
        $reportId = m::mock(UuidInterface::class);
        $reportId->shouldReceive('toString')->andReturn('abcd');
        $issueId = 'CONOPS-19';
        $entity = new Entity(new EntityId('meh'), EntityType::IdP());
        $failedTestName = 'test.name';

        $report = JiraReport::trackIssue($reportId, $issueId, $entity, $failedTestName);

        /** @var MockInterface|JiraReportRepository $reportRepository */
        $reportRepository = m::mock(JiraReportRepository::class);
        $reportRepository->shouldReceive('add')->once()->with(m::anyOf($report));

        $service = new JiraReportService($reportRepository, new NullLogger());
        $service->trackNewIssue($reportId, $issueId, $entity, $failedTestName);
    }
}
