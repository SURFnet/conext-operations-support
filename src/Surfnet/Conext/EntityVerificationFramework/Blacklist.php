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

namespace Surfnet\Conext\EntityVerificationFramework;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationBlacklist;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;

final class Blacklist implements VerificationBlacklist
{
    const WILDCARD = '*';

    /**
     * @var EntitySet[]
     */
    private $entitiesBySuiteOrTestName;

    /**
     * @var EntitySet
     */
    private $wildcardEntities;

    /**
     * @param EntitySet[] $entitiesBySuiteOrTestName
     * @param EntitySet   $wildcardEntities
     */
    public function __construct(array $entitiesBySuiteOrTestName, EntitySet $wildcardEntities)
    {
        Assert::allIsInstanceOf($entitiesBySuiteOrTestName, EntitySet::class);

        $suiteOrTestNames = array_keys($entitiesBySuiteOrTestName);
        Assert::allString($suiteOrTestNames);

        $this->entitiesBySuiteOrTestName = $entitiesBySuiteOrTestName;
        $this->wildcardEntities = $wildcardEntities;
    }

    public function isBlacklisted(Entity $entity, $suiteOrTestName)
    {
        if ($this->wildcardEntities->contains($entity)) {
            return true;
        }

        if (array_key_exists($suiteOrTestName, $this->entitiesBySuiteOrTestName)) {
            return $this->entitiesBySuiteOrTestName[$suiteOrTestName]->contains($entity);
        }

        return false;
    }
}
