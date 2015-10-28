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

namespace Surfnet\Conext\OperationsSupportBundle\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\JiraApiClientBundle\Dto\Issue;

final class JiraIssue
{
    /**
     * @var JiraIssuePriority
     */
    private $priority;

    /**
     * @var JiraIssueStatus
     */
    private $status;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $description;

    /**
     * @param Issue $dto
     * @return JiraIssue
     */
    public static function fromIssueDto(Issue $dto)
    {
        return new JiraIssue(
            new JiraIssuePriority($dto->priorityId),
            new JiraIssueStatus($dto->statusId),
            $dto->summary,
            $dto->description
        );
    }

    /**
     * @param JiraIssuePriority $priority
     * @param JiraIssueStatus   $status
     * @param string            $summary
     * @param string            $description
     */
    public function __construct(JiraIssuePriority $priority, JiraIssueStatus $status, $summary, $description)
    {
        Assert::string($summary, 'Summary must be a string');
        Assert::string($description, 'Description must be a string');

        $this->priority = $priority;
        $this->status = $status;
        $this->summary = $summary;
        $this->description = $description;
    }

    /**
     * @param string $summary
     * @param string $description
     * @return bool
     */
    public function summaryAndDescriptionEqual($summary, $description)
    {
        Assert::string($summary, 'Summary must be a string');
        Assert::string($description, 'Description must be a string');

        return $this->summary === $summary && $this->description === $description;
    }

    /**
     * @param JiraIssuePriority $priority
     * @return bool
     */
    public function priorityEquals(JiraIssuePriority $priority)
    {
        return $this->priority->equals($priority);
    }

    /**
     * @param JiraIssueStatus $status
     * @return bool
     */
    public function statusEquals(JiraIssueStatus $status)
    {
        return $this->status->equals($status);
    }
}
