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

namespace Surfnet\Conext\OperationsSupportBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;

final class DoctrineOrmJiraReportRepository extends EntityRepository implements JiraReportRepository
{
    public function findMostRecentlyReported(Entity $entity, $testName)
    {
        $reports = $this->createQueryBuilder('r')
            ->where('r.entityId = :entityId')
            ->setParameter('entityId', $entity->getEntityId())
            ->andWhere('r.entityType = :entityType')
            ->setParameter('entityType', $entity->getEntityType())
            ->andWhere('r.testName = :testName')
            ->setParameter('testName', $testName)
            ->orderBy('r.reportedOn', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        switch (count($reports)) {
            case 0:
                return null;
            case 1:
                return current($reports);
            default:
                throw new LogicException(sprintf('Limited query to one result, yet got %d results', count($reports)));
        }
    }

    public function add(JiraReport $report)
    {
        if ($this->_em->contains($report)) {
            throw new LogicException('Cannot add JIRA report; given report is already known');
        }

        $this->_em->persist($report);
        $this->_em->flush();
    }

    /**
     * @param JiraReport $report
     * @return void
     */
    public function replace(JiraReport $report)
    {
        if (!$this->_em->contains($report)) {
            throw new LogicException('Cannot replace JIRA report; given report was not retrieved via this repository');
        }

        $this->_em->persist($report);
        $this->_em->flush();
    }
}
