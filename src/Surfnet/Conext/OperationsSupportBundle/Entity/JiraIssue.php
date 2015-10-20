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

namespace Surfnet\Conext\OperationsSupportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;

/**
 * @ORM\Embeddable
 */
class JiraIssue
{
    /**
     * @ORM\Column
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="ops_jira_issue_status")
     *
     * @var JiraIssueStatus
     */
    private $status;

    /**
     * @ORM\Column(type="ops_jira_issue_priority")
     *
     * @var JiraIssuePriority
     */
    private $priority;

    /**
     * @ORM\Column
     *
     * @var string
     */
    private $summary;

    /**
     * @ORM\Column
     *
     * @var string
     */
    private $description;

    /**
     * @param string            $id
     * @param JiraIssueStatus   $status
     * @param JiraIssuePriority $priority
     * @param string            $summary
     * @param string            $description
     */
    public function __construct($id, JiraIssueStatus $status, JiraIssuePriority $priority, $summary, $description)
    {
        Assert::string($id, 'Issue ID must be string');
        Assert::notBlank($id, 'Issue ID may not be blank');
        Assert::string($summary, 'Summary must be string');
        Assert::notBlank($summary, 'Summary may not be blank');
        Assert::string($description, 'Description must be string');
        Assert::notBlank($description, 'Description may not be blank');

        $this->id          = $id;
        $this->status      = $status;
        $this->priority    = $priority;
        $this->summary     = $summary;
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isMuted()
    {
        return $this->status->isMuted();
    }

    /**
     * @param JiraIssue $other
     * @return bool
     */
    public function equals(JiraIssue $other)
    {
        return $this->id === $other->id
            && $this->status->equals($other->status)
            && $this->priority->equals($other->priority)
            && $this->summary === $other->summary
            && $this->description === $other->description;
    }
}
