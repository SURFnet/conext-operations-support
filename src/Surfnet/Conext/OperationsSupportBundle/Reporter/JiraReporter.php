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

namespace Surfnet\Conext\OperationsSupportBundle\Reporter;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidFactoryInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraIssueService;
use Surfnet\Conext\OperationsSupportBundle\Service\JiraReportService;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;

final class JiraReporter implements VerificationReporter
{
    const REPORT = <<<REPORT
Test "%s" failed for entity "%s".

%s
REPORT;

    /**
     * @var JiraReportService
     */
    private $reportService;

    /**
     * @var JiraIssueService
     */
    private $issueService;

    /**
     * @var UuidFactoryInterface
     */
    private $uuidFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JiraReportService $reportService,
        JiraIssueService $issueService,
        UuidFactoryInterface $uuidFactory,
        LoggerInterface $logger
    ) {
        $this->reportService = $reportService;
        $this->issueService = $issueService;
        $this->uuidFactory = $uuidFactory;
        $this->logger = $logger;
    }

    public function reportFailedVerificationFor(Entity $entity, VerificationSuiteResult $result)
    {
        if (!$result->hasTestFailed()) {
            throw new LogicException('Cannot report test that has not failed');
        }

        $failedTestName = $result->getFailedTestName();

        $this->logger->info(
            sprintf(
                'Reporting "%s" failure "%s" for entity "%s"',
                $failedTestName,
                $result->getReason(),
                $entity
            )
        );

        $report = $this->reportService->findMostRecentlyReported($entity, $failedTestName);

        if ($report === null) {
            $this->logger->info('No report, creating JIRA issue and tracking report');

            $issueId = $this->issueService->createIssue(
                $this->issueService->mapStatusToJiraStatusId(JiraIssueStatus::OPEN),
                $this->issueService->mapSeverityToJiraPriorityId($result->getSeverity()),
                $result->getReason(),
                sprintf(
                    self::REPORT,
                    $failedTestName,
                    $entity,
                    $result->getExplanation()
                )
            );

            $reportId = $this->uuidFactory->uuid4();
            $this->reportService->trackNewIssue($reportId, $issueId, $entity, $failedTestName);
        }
    }
}
