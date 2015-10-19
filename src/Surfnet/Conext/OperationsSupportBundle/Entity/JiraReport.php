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

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\DateTime\DateTime;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;

/**
 * @ORM\Entity(repositoryClass="Surfnet\Conext\OperationsSupportBundle\Repository\DoctrineOrmJiraReportRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="conops_jirareport_uniq_entity_id_type_testname",
 *         columns={"entity_id", "entity_type", "test_name", "issue_id"}
 *     )
 * })
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateFields) -- Fields id, issueId and reportedOn are used only for querying.
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
    private $issueId;

    /**
     * @ORM\Column(type="datetime_immutable")
     *
     * @var DateTimeImmutable
     */
    private $reportedOn;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     *
     * @var DateTimeImmutable
     */
    private $mutedSince;

    /**
     * @param UuidInterface $reportId
     * @param Entity        $entity
     * @param string        $testName
     * @param string        $issueId
     * @return JiraReport
     */
    public static function trackIssue(UuidInterface $reportId, Entity $entity, $testName, $issueId)
    {
        Assert::string($testName, 'Test name must be string');
        Assert::notBlank($testName, 'Test name may not be blank');
        Assert::string($issueId, 'Issue ID must be string');
        Assert::notBlank($issueId, 'Issue ID may not be blank');

        $report = new JiraReport();
        $report->id         = $reportId->toString();
        $report->entityId   = $entity->getEntityId();
        $report->entityType = $entity->getEntityType();
        $report->testName   = $testName;
        $report->issueId    = $issueId;
        $report->reportedOn = DateTime::now();

        return $report;
    }

    private function __construct()
    {
    }

    /**
     * When a JIRA issue has been resolved or muted for too long, and as such needs reopening, the report will track
     * a new JIRA issue.
     *
     * @param UuidInterface $reportId
     * @param string        $issueId
     * @return JiraReport
     */
    public function trackUsingNewIssue(UuidInterface $reportId, $issueId)
    {
        Assert::string($issueId, 'Issue ID must be string');
        Assert::notBlank($issueId, 'Issue ID may not be blank');

        $report = new JiraReport();
        $report->id         = $reportId->toString();
        $report->entityId   = $this->entityId;
        $report->entityType = $this->entityType;
        $report->testName   = $this->testName;
        $report->issueId    = $issueId;
        $report->reportedOn = DateTime::now();

        return $report;
    }

    /**
     * Marks the report as muted. Muted reports may be tracked using a new issue in the future.
     */
    public function mute()
    {
        if ($this->mutedSince) {
            throw new LogicException('JIRA report already muted');
        }

        $this->mutedSince = DateTime::now();
    }

    /**
     * @param DateInterval $interval
     * @return bool
     */
    public function hasBeenMutedForMoreThan(DateInterval $interval)
    {
        return $this->mutedSince && $this->mutedSince->add($interval) < DateTime::now();
    }

    /**
     * @return bool
     */
    public function isMuted()
    {
        return $this->mutedSince !== null;
    }
}
