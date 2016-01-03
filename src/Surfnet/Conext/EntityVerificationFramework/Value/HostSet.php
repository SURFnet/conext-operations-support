<?php

/**
 * Copyright 2016 SURFnet B.V.
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

final class HostSet implements IteratorAggregate, Countable
{
    /**
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @param Host[] $hosts
     */
    public function __construct(array $hosts = [])
    {
        foreach ($hosts as $host) {
            $this->add($host);
        }
    }

    /**
     * @param Host $host
     */
    public function add(Host $host)
    {
        if ($this->contains($host)) {
            return;
        }

        $this->hosts[] = $host;
    }

    /**
     * @param Host $host The host to search for.
     * @return boolean TRUE if the collection contains the element, FALSE otherwise.
     */
    public function contains(Host $host)
    {
        foreach ($this->hosts as $existingUrl) {
            if ($host->equals($existingUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable $mapper (Host):mixed
     * @return mixed[]
     */
    public function map(callable $mapper)
    {
        return array_map($mapper, $this->hosts);
    }

    /**
     * @param HostSet $other
     * @return bool
     */
    public function equals(HostSet $other)
    {
        if (count($this->hosts) !== count($other->hosts)) {
            return false;
        }

        foreach ($this->hosts as $host) {
            if (!$other->contains($host)) {
                return false;
            }
        }

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->hosts);
    }

    public function count()
    {
        return count($this->hosts);
    }
}
