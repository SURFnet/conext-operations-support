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

final class AssertionConsumerServiceList implements IteratorAggregate, Countable
{
    /**
     * @var AssertionConsumerService[]
     */
    private $acss;

    /**
     * @param array  $data
     * @param string $propertyPath
     * @return AssertionConsumerServiceList
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::isArray(
            $data,
            'SP metadata\'s "AssertionConsumerService" key must contain an array',
            $propertyPath
        );

        $list = new self();
        $list->acss = array_map(
            function ($data) {
                return AssertionConsumerService::deserialise($data);
            },
            $data
        );

        return $list;
    }

    public function __construct(array $acss = [])
    {
        Assert::allIsInstanceOf($acss, AssertionConsumerService::class);

        $this->acss = $acss;
    }

    /**
     * @param AssertionConsumerService $service
     * @return AssertionConsumerServiceList
     */
    public function add(AssertionConsumerService $service)
    {
        return new AssertionConsumerServiceList(array_merge($this->acss, [$service]));
    }

    public function getIterator()
    {
        return new ArrayIterator($this->acss);
    }

    public function count()
    {
        return count($this->acss);
    }

    public function __toString()
    {
        return sprintf('AssertionConsumerServiceList(%s)', join(', ', array_map('strval', $this->acss)));
    }
}
