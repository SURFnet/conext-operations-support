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

use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

final class BlacklistFactory
{
    /**
     * @param array[][] $entitiesBySuiteOrTestName
     * @param array[]   $wildcardEntities
     * @return Blacklist
     */
    public static function fromDescriptors(array $entitiesBySuiteOrTestName)
    {
        $suiteOrTestNames = array_keys($entitiesBySuiteOrTestName);
        Assert::allString($suiteOrTestNames, 'Suite and test names must be strings');

        return new Blacklist(
            array_map(
                function (array $entityDescriptors) {
                    return self::createEntitySetFromEntityDescriptors($entityDescriptors);
                },
                $entitiesBySuiteOrTestName
            )
        );
    }

    /**
     * @param array[] $entityDescriptors
     * @return EntitySet
     */
    private static function createEntitySetFromEntityDescriptors(array $entityDescriptors)
    {
        Assert::allIsArray($entityDescriptors, 'Entity descriptors must consist of arrays');

        return new EntitySet(
            array_map(
                function (array $entityDescriptor) {
                    Assert::count($entityDescriptor, 2);

                    switch ($entityDescriptor[1]) {
                        case 'sp':
                            return new Entity(new EntityId($entityDescriptor[0]), EntityType::SP());
                        case 'idp':
                            return new Entity(new EntityId($entityDescriptor[0]), EntityType::IdP());
                        default:
                            throw new LogicException('Entity descriptor type neither "sp" nor "idp"');
                    }
                },
                $entityDescriptors
            )
        );
    }
}
