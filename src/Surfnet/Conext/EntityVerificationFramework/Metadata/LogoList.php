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

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathConstraintViolationWriter;

final class LogoList implements ConfiguredMetadataValidatable
{
    /**
     * @var Logo[]
     */
    private $logos;

    /**
     * @param mixed  $data
     * @param string $propertyPath
     * @return LogoList
     */
    public static function deserialize($data, $propertyPath)
    {
        $list = new self();
        $list->logos = array_map(
            function ($data) use ($propertyPath) {
                return Logo::deserialize($data, $propertyPath . '[]');
            },
            $data
        );

        return $list;
    }

    /**
     * @param Logo[] $logos
     */
    public function __construct(array $logos = [])
    {
        Assert::allIsInstanceOf($logos, Logo::class);

        $this->logos = $logos;
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        foreach ($this->logos as $i => $logo) {
            $visitor->visit($logo, new SubpathConstraintViolationWriter($violations, 'Logo #' . ($i + 1)), $context);
        }
    }
}
