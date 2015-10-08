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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

final class EntitySet implements Countable, IteratorAggregate
{
    /**
     * @var Entity[]
     */
    private $entities = [];

    /**
     * @param Entity[] $entities
     */
    public function __construct(array $entities)
    {
        foreach ($entities as $entity) {
            $this->add($entity);
        }
    }

    /**
     * Tests sets for equality. Note this is a O(N^2) operation.
     *
     * @param EntitySet $other
     * @return bool
     */
    public function equals(EntitySet $other)
    {
        if (count($this->entities) !== count($other->entities)) {
            return false;
        }

        $unequalOtherEntities = $other->entities;
        foreach ($this->entities as $myEntity) {
            foreach ($unequalOtherEntities as $key => $otherEntity) {
                if ($myEntity->equals($otherEntity)) {
                    unset($unequalOtherEntities[$key]);
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->entities);
    }

    public function count()
    {
        return count($this->entities);
    }

    /**
     * @param Entity $entity
     * @return boolean
     */
    private function add(Entity $entity)
    {
        if ($this->contains($entity)) {
            return false;
        }

        $this->entities[] = $entity;
        return true;
    }

    /**
     * @param Entity $entity The entity to search for.
     *
     * @return boolean TRUE if the collection contains the element, FALSE otherwise.
     */
    private function contains(Entity $entity)
    {
        foreach ($this->entities as $existingEntity) {
            if ($entity->equals($existingEntity)) {
                return true;
            }
        }

        return false;
    }
}
