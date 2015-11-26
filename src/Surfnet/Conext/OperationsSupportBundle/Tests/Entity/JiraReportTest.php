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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Entity;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\DateTime\DateTime;
use Surfnet\Conext\OperationsSupportBundle\Entity\JiraReport;

class JiraReportTest extends TestCase
{
    /**
     * @test
     * @group jira
     */
    public function an_issue_can_be_tracked()
    {
        JiraReport::trackIssue($this->uuid(), 'CONOPS-91', $this->entity(), 'test.name');
    }

    /**
     * @return UuidInterface
     */
    private function uuid()
    {
        return Uuid::uuid4();
    }

    /**
     * @return \Surfnet\Conext\EntityVerificationFramework\Value\Entity
     */
    private function entity()
    {
        return new Entity(new EntityId('meh'), EntityType::IdP());
    }
}
