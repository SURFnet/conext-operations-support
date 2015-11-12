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

namespace Surfnet\Conext\EntityVerificationFramework\Metadata;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Surfnet\Conext\EntityVerificationFramework\Assert;

final class NameIdFormatList implements IteratorAggregate, Countable
{
    /**
     * @var NameIdFormat[]
     */
    private $formats;

    /**
     * @param NameIdFormat[] $formats
     */
    public function __construct(array $formats = [])
    {
        Assert::allIsInstanceOf($formats, NameIdFormat::class);

        $this->formats = $formats;
    }

    /**
     * @param NameIdFormat $format
     * @return NameIdFormatList
     */
    public function add(NameIdFormat $format)
    {
        return new NameIdFormatList(array_merge($this->formats, [$format]));
    }

    public function count()
    {
        return count($this->formats);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->formats);
    }
}
