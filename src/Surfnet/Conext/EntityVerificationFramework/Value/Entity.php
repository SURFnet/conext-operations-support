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

namespace Surfnet\Conext\EntityVerificationFramework\Value;

final class Entity
{
    /**
     * @var EntityId
     */
    private $entityId;
    /**
     * @var EntityType
     */
    private $entityType;

    public function __construct(EntityId $entityId, EntityType $entityType)
    {
        $this->entityId = $entityId;
        $this->entityType = $entityType;
    }

    /**
     * @return EntityId
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return EntityType
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param Entity $other
     * @return bool
     */
    public function equals(Entity $other)
    {
        return $this->entityId->equals($other->entityId) && $this->entityType->equals($other->entityType);
    }

    public function __toString()
    {
        return $this->entityId . '[' . $this->entityType . ']';
    }
}
