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
    const ISSUE_DESCRIPTION = <<<DESCRIPTION
Test "%s" failed for entity "%s".

%s
DESCRIPTION;

    const COMMENT_BODY = <<<BODY
*Test failure reason/explanation have changed*

%s:
%s
BODY;

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

        $this->logger->info(
            sprintf(
                'Reporting "%s" failure "%s" for entity "%s"',
                $result->getFailedTestName(),
                $result->getReason(),
                $entity
            )
        );

        $report           = $this->reportService->findMostRecentlyReported($entity, $result->getFailedTestName());
        $issuePriority    = $this->issueService->mapSeverityToJiraPriorityId($result->getSeverity());
        $issueDescription = sprintf(
            self::ISSUE_DESCRIPTION,
            $result->getFailedTestName(),
            $entity,
            $result->getExplanation()
        );
        $commentBody = sprintf(self::COMMENT_BODY, $result->getReason(), $result->getExplanation());

        // Track a new issue using a report if we're not tracking an issue...
        if ($report === null) {
            $this->logger->info('No report, creating JIRA issue and tracking report');

            $issueKey = $this->issueService->createIssue(
                $issuePriority,
                $result->getReason(),
                $issueDescription
            );

            $reportId = $this->uuidFactory->uuid4();
            $this->reportService->trackNewIssue($reportId, $issueKey, $entity, $result->getFailedTestName());

            return;
        }

        $issueKey = $report->getIssueKey();
        $issue    = $this->issueService->getIssue($issueKey);
        if ($issue->statusEquals($this->issueService->mapStatusToJiraStatusId(JiraIssueStatus::MUTED))) {
            return;
        }

        // ... or track a new issue using a new report if the previous issue has already been closed...
        if ($issue->statusEquals($this->issueService->mapStatusToJiraStatusId(JiraIssueStatus::CLOSED))) {
            $this->logger->info('JIRA issue is closed, creating new issue and tracking using new report');

            $issueKey = $this->issueService->createIssue(
                $issuePriority,
                $result->getReason(),
                $issueDescription
            );

            $reportId = $this->uuidFactory->uuid4();
            $this->reportService->trackNewIssue($reportId, $issueKey, $entity, $result->getFailedTestName());

            return;
        }

        // ... or reprioritise...
        if (!$issue->priorityEquals($issuePriority)) {
            $this->issueService->reprioritiseIssue($issueKey, $issuePriority);
        }

        $mostRecentCommentId         = $report->getMostRecentCommentId();
        $mostRecentCommentIsUpToDate = $report->wasCommentedOn()
            && $this->issueService->getComment($issueKey, $mostRecentCommentId)->bodyEquals($commentBody);

        if ($mostRecentCommentIsUpToDate) {
            return;
        } elseif ($issue->summaryAndDescriptionEqual($result->getReason(), $issueDescription)) {
            return;
        }

        // ... and comment on, if needed.
        $commentId = $this->issueService->commentOnIssue($issueKey, $commentBody);
        $report->addComment($commentId);

        $this->reportService->updateReport($report);
    }
}
