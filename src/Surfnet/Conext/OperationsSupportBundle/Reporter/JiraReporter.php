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
use Ramsey\Uuid\Uuid;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraIssue;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;
use Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException;
use Surfnet\Conext\OperationsSupportBundle\Repository\JiraReportRepository;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Service\IssueService;

final class JiraReporter implements VerificationReporter
{
    /**
     * @var JiraReportRepository
     */
    private $reportRepository;

    /**
     * @var IssueService
     */
    private $issueService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JiraReportRepository $reportRepository,
        IssueService $issueService,
        LoggerInterface $logger
    ) {
        $this->reportRepository = $reportRepository;
        $this->issueService = $issueService;
        $this->logger = $logger;
    }

    public function reportFailedVerificationFor(Entity $entity, VerificationSuiteResult $result)
    {
        if (!$result->hasTestFailed()) {
            throw new LogicException('Cannot report test that has not failed');
        }

        $this->logger->info(
            sprintf(
                'Reporting "%s" failure "%s" for entity "%s"',
                $result->getFailedTestName(),
                $result->getReason(),
                $entity
            )
        );

        $report = $this->reportRepository->findMostRecentlyReported($entity, $result->getFailedTestName());

        if ($report === null) {
            $this->logger->info('No report, creating JIRA issue and tracking report');
            $this->createAndTrackIssue($entity, $result);
        } else {
            // Not yet implemented.
        }
    }

    /**
     * @param Entity                  $entity
     * @param VerificationSuiteResult $result
     */
    private function createAndTrackIssue(Entity $entity, VerificationSuiteResult $result)
    {
        $issue    = $this->createIssue($result);
        $reportId = Uuid::uuid4();
        $report   = JiraReport::trackIssue($reportId, $entity, $result->getFailedTestName(), $issue);

        $this->reportRepository->add($report);

        $this->logger->info(sprintf('JIRA issue is tracked in "%s"', $reportId->toString()));
    }

    /**
     * @param VerificationSuiteResult $result
     * @return JiraIssue
     */
    private function createIssue(VerificationSuiteResult $result)
    {
        $priority    = JiraIssuePriority::forSeverity($result->getSeverity());
        $summary     = $result->getReason();
        $description = $result->getExplanation();

        $command = new CreateIssueCommand();
        $command->priority    = $priority->getPriorityId();
        $command->summary     = $summary;
        $command->description = $description;

        $createIssueResult = $this->issueService->createIssue($command);

        if ($createIssueResult->wasClientErrorReported()) {
            throw new RuntimeException(
                sprintf(
                    'JIRA issue creation unexpectedly failed due to API client error: "%s"',
                    join(', ', $createIssueResult->getErrorMessages())
                )
            );
        }

        $this->logger->info(sprintf('Reported failure in JIRA issue "%s"', $createIssueResult->getIssueId()));

        return new JiraIssue(
            $createIssueResult->getIssueId(),
            new JiraIssueStatus($createIssueResult->getStatusId()),
            $priority,
            $summary,
            $description
        );
    }
}
