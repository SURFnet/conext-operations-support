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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathValidator;

final class ShibbolethMetadataScopeList implements ConfiguredMetadataValidatable, IteratorAggregate, Countable
{
    /**
     * @var ShibbolethMetadataScope[]
     */
    private $scopes = [];

    /**
     * @param mixed $data An array of ShibbolethMetadataScope array structures
     * @param string $propertyPath
     * @return ShibbolethMetadataScopeList
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::isArray($data, 'List of ShibbolethMetadataScopes must be array, got "%s"');

        $list = new ShibbolethMetadataScopeList();
        foreach (array_values($data) as $i => $scopeData) {
            $list->scopes[] = ShibbolethMetadataScope::deserialise($scopeData, sprintf('%s[%d]', $propertyPath, $i));
        }

        return $list;
    }

    /**
     * @param ShibbolethMetadataScope[] $scopes
     */
    public function __construct(array $scopes = [])
    {
        Assert::allIsInstanceOf($scopes, ShibbolethMetadataScope::class);

        $this->scopes = $scopes;
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        foreach ($this->scopes as $i => $scope) {
            $subpathValidator = new SubpathValidator($validator, 'ShibbolethMetadataScope #' . ($i + 1));
            $subpathValidator->validate($scope, $context);
        }
    }

    /**
     * @param ShibbolethMetadataScope $scope
     * @return ShibbolethMetadataScopeList
     */
    public function add(ShibbolethMetadataScope $scope)
    {
        return new ShibbolethMetadataScopeList(array_merge($this->scopes, [$scope]));
    }

    public function getIterator()
    {
        return new ArrayIterator($this->scopes);
    }

    public function count()
    {
        return count($this->scopes);
    }

    public function __toString()
    {
        return sprintf('ShibbolethMetadataScopeList(%s)', join(', ', array_map('strval', $this->scopes)));
    }
}
