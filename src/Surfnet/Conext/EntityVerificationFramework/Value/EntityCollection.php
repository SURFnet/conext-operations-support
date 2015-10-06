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

final class EntityCollection implements Countable, IteratorAggregate
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
            $this->initializeWith($entity);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->entities);
    }

    public function count()
    {
        return count($this->entities);
    }

    private function initializeWith(Entity $entity)
    {
        if (in_array($entity, $this->entities)) {
            throw new LogicException(sprintf(
                'Entity "%s" has already been added, cannot add it again',
                $entity
            ));
        }

        $this->entities[] = $entity;
    }
}