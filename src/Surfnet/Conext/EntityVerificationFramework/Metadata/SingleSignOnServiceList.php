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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathValidator;

final class SingleSignOnServiceList implements ConfiguredMetadataValidatable, IteratorAggregate, Countable
{
    /**
     * @var SingleSignOnService[]
     */
    private $ssos;

    /**
     * @param array  $data
     * @param string $propertyPath
     * @return SingleSignOnServiceList
     */
    public static function deserialize($data, $propertyPath)
    {
        Assert::isArray(
            $data,
            'SP metadata\'s "SingleSignOnService" key must contain an array',
            $propertyPath
        );

        $list = new self();
        $list->ssos = array_map(
            function ($data) {
                return SingleSignOnService::deserialize($data);
            },
            $data
        );

        return $list;
    }

    /**
     * @param SingleSignOnService[] $ssos
     */
    public function __construct(array $ssos = [])
    {
        Assert::allIsInstanceOf($ssos, SingleSignOnService::class);

        $this->ssos = $ssos;
    }

    /**
     * @param SingleSignOnService $service
     * @return SingleSignOnServiceList
     */
    public function add(SingleSignOnService $service)
    {
        return new SingleSignOnServiceList(array_merge($this->ssos, [$service]));
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        foreach ($this->ssos as $i => $sso) {
            $subpathValidator = new SubpathValidator($validator, 'SingleSignOnService #' . ($i + 1));
            $subpathValidator->validate($sso, $context);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->ssos);
    }

    public function count()
    {
        return count($this->ssos);
    }

    public function __toString()
    {
        return sprintf('SingleSignOnServiceList(%s)', join(', ', array_map('strval', $this->ssos)));
    }
}
