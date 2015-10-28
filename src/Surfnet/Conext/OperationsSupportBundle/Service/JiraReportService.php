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

namespace Surfnet\Conext\OperationsSupportBundle\Service;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Repository\JiraReportRepository;

class JiraReportService
{
    /**
     * @var JiraReportRepository
     */
    private $reportRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JiraReportRepository $reportRepository,
        LoggerInterface $logger
    ) {
        $this->reportRepository = $reportRepository;
        $this->logger           = $logger;
    }

    /**
     * @param UuidInterface $id
     * @param string        $issueKey
     * @param Entity        $entity
     * @param string        $failedTestName
     * @return JiraReport
     */
    public function trackNewIssue(UuidInterface $id, $issueKey, Entity $entity, $failedTestName)
    {
        $report = JiraReport::trackIssue($id, $issueKey, $entity, $failedTestName);

        $this->reportRepository->add($report);

        $this->logger->info(sprintf('JIRA issue is tracked in report "%s"', $id->toString()));

        return $report;
    }

    /**
     * @param JiraReport $report
     */
    public function updateReport(JiraReport $report)
    {
        $this->reportRepository->replace($report);
    }

    /**
     * @param Entity $entity
     * @param string $testName
     * @return JiraReport|null
     */
    public function findMostRecentlyReported(Entity $entity, $testName)
    {
        return $this->reportRepository->findMostRecentlyReported($entity, $testName);
    }
}
