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
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;

final class Blacklist implements VerificationBlacklist
{
    const WILDCARD = '*';

    /**
     * @var EntitySet[] Array of EntitySets, indexed by a suite name, test name or wildcard symbol.
     */
    private $entitiesBySuiteOrTestName;

    /**
     * @param EntitySet[] $entitiesBySuiteOrTestName
     */
    public function __construct(array $entitiesBySuiteOrTestName)
    {
        Assert::allIsInstanceOf($entitiesBySuiteOrTestName, EntitySet::class);

        $suiteOrTestNames = array_keys($entitiesBySuiteOrTestName);
        Assert::allString($suiteOrTestNames);

        if (!array_key_exists(self::WILDCARD, $entitiesBySuiteOrTestName)) {
            $entitiesBySuiteOrTestName[self::WILDCARD] = new EntitySet();
        }

        $this->entitiesBySuiteOrTestName = $entitiesBySuiteOrTestName;
    }

    public function isBlacklisted(Entity $entity, $suiteOrTestName)
    {
        if ($suiteOrTestName === self::WILDCARD) {
            throw new InvalidArgumentException(
                sprintf('The wildcard symbol ("%s") is not a valid suite or test name', self::WILDCARD)
            );
        }

        if ($this->entitiesBySuiteOrTestName[self::WILDCARD]->contains($entity)) {
            return true;
        }

        if (array_key_exists($suiteOrTestName, $this->entitiesBySuiteOrTestName)) {
            return $this->entitiesBySuiteOrTestName[$suiteOrTestName]->contains($entity);
        }

        return false;
    }
}
