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
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

final class PublishedMetadataList implements IteratorAggregate, Countable
{
    /**
     * @var PublishedMetadata[]
     */
    private $metadatas;

    /**
     * @param PublishedMetadata[] $metadatas
     */
    public function __construct(array $metadatas = [])
    {
        Assert::allIsInstanceOf($metadatas, PublishedMetadata::class);

        $this->metadatas = array_values($metadatas);
    }

    /**
     * @param PublishedMetadata $metadata
     * @return PublishedMetadataList
     */
    public function add(PublishedMetadata $metadata)
    {
        return new PublishedMetadataList(array_merge($this->metadatas, [$metadata]));
    }

    /**
     * @param Entity $entity
     * @return PublishedMetadataList
     */
    public function findByEntity(Entity $entity)
    {
        return $this->filter(function (PublishedMetadata $metadata) use ($entity) {
            return $metadata->getEntity()->equals($entity);
        });
    }

    /**
     * @return PublishedMetadata|null
     */
    public function first()
    {
        if (count($this->metadatas) === 0) {
            throw new LogicException('Cannot fetch first published metadata from list; list contains no metadata');
        }

        return $this->metadatas[0];
    }

    public function count()
    {
        return count($this->metadatas);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->metadatas);
    }

    /**
     * @param callable $predicate
     * @return PublishedMetadataList
     */
    private function filter(callable $predicate)
    {
        return new PublishedMetadataList(array_filter($this->metadatas, $predicate));
    }
}
