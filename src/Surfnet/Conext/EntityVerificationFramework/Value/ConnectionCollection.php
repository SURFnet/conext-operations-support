<?php

/**
 * Copyright 2015 SURFnet bv
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

final class ConnectionCollection implements Countable, IteratorAggregate
{
    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @param Connection[] $connections
     */
    public function __construct(array $connections)
    {
        foreach ($connections as $connection) {
            $this->initializeWith($connection);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->connections);
    }

    public function count()
    {
        return count($this->connections);
    }

    private function initializeWith(Connection $connection)
    {
        $entities = array_map(function (Connection $connection) {
            return $connection->getEntity();
        }, $this->connections);

        $entityToAdd = $connection->getEntity();
        if (in_array($entityToAdd, $entities)) {
            throw new LogicException(sprintf(
                'Connection "%s" has already been added, cannot add it again',
                $connection->getEntity()
            ));
        }

        $this->connections[] = $connection;
    }
}
