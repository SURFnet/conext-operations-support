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
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraIssue;
use Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Service\IssueService;

class JiraIssueService
{
    /**
     * @var IssueService
     */
    private $issueApiService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(IssueService $issueApiService, LoggerInterface $logger)
    {
        $this->issueApiService = $issueApiService;
        $this->logger          = $logger;
    }

    /**
     * @param JiraIssueStatus   $status
     * @param JiraIssuePriority $priority
     * @param string            $summary
     * @param string            $description
     * @return JiraIssue
     */
    public function createIssue(JiraIssueStatus $status, JiraIssuePriority $priority, $summary, $description)
    {
        $command = new CreateIssueCommand();
        $command->statusId    = $status->getStatusId();
        $command->priorityId  = $priority->getPriorityId();
        $command->summary     = $summary;
        $command->description = $description;

        $createIssueResult = $this->issueApiService->createIssue($command);

        if ($createIssueResult->wasClientErrorReported()) {
            throw new RuntimeException(
                sprintf(
                    'JIRA issue creation unexpectedly failed due to API client error: "%s"',
                    join(', ', $createIssueResult->getErrorMessages())
                )
            );
        }

        $this->logger->info(sprintf('Reported failure in JIRA issue "%s"', $createIssueResult->getIssueId()));

        return new JiraIssue($createIssueResult->getIssueId(), $status, $priority, $summary, $description);
    }
}
