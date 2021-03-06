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

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;

/**
 * @ORM\Entity(repositoryClass="Surfnet\Conext\OperationsSupportBundle\Repository\DoctrineOrmJiraReportRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="conops_jirareport_uniq_entity_id_type_testname",
 *         columns={"entity_id", "entity_type", "test_name", "issue_key"}
 *     )
 * })
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateFields) -- Fields id, issueKey and reportedOn are used only for querying.
 */
class JiraReport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=36)
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="evf_entity_id")
     *
     * @var EntityId
     */
    private $entityId;

    /**
     * @ORM\Column(type="evf_entity_type")
     *
     * @var EntityType
     */
    private $entityType;

    /**
     * @ORM\Column
     *
     * @var string
     */
    private $testName;

    /**
     * @ORM\Column
     *
     * @var string
     */
    private $issueKey;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string|null
     */
    private $mostRecentCommentId;

    /**
     * @ORM\Column(type="datetime_immutable")
     *
     * @var DateTimeImmutable
     */
    private $reportedOn;

    /**
     * @param UuidInterface $reportId
     * @param string        $issueKey
     * @param Entity        $entity
     * @param string        $testName
     * @return JiraReport
     */
    public static function trackIssue(UuidInterface $reportId, $issueKey, Entity $entity, $testName)
    {
        Assert::string($issueKey, 'Issue key must be string');
        Assert::notBlank($issueKey, 'Issue key may not be blank');
        Assert::string($testName, 'Test name must be string');
        Assert::notBlank($testName, 'Test name may not be blank');

        $report = new JiraReport();
        $report->id         = $reportId->toString();
        $report->entityId   = $entity->getEntityId();
        $report->entityType = $entity->getEntityType();
        $report->testName   = $testName;
        $report->issueKey   = $issueKey;
        $report->reportedOn = new DateTimeImmutable();

        return $report;
    }

    private function __construct()
    {
    }

    /**
     * @param string $commentId
     */
    public function addComment($commentId)
    {
        Assert::string($commentId, 'Comment ID must be string');
        Assert::notBlank($commentId, 'Comment ID may not be blank');

        $this->mostRecentCommentId = $commentId;
    }

    /**
     * @return string
     */
    public function getIssueKey()
    {
        return $this->issueKey;
    }

    /**
     * @return bool
     */
    public function wasCommentedOn()
    {
        return $this->mostRecentCommentId !== null;
    }

    /**
     * @return string
     */
    public function getMostRecentCommentId()
    {
        if (!$this->wasCommentedOn()) {
            throw new LogicException('JIRA issue was never commented on');
        }

        return $this->mostRecentCommentId;
    }
}
