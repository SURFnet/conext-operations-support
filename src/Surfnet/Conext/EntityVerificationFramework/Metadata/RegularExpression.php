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

final class RegularExpression implements ConfiguredMetadataValidatable
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        Assert::string($pattern, 'Regular expression "%s" must be a string, got "%s"');

        $this->pattern = $pattern;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        set_error_handler(function ($level, $message) use ($violations) {
            $violations->add(sprintf(
                'An error would occur during execution of regular expression "%s": "%s"',
                $this->pattern,
                $message
            ));
        });
        $result = preg_match($this->pattern, 'test');
        restore_error_handler();

        if ($result === false) {
            $violations->add('Regular expression would not execute: it is somehow invalid');
        }
    }

    /**
     * @param RegularExpression $other
     * @return bool
     */
    public function equals(RegularExpression $other)
    {
        return $this->pattern === $other->pattern;
    }

    public function __toString()
    {
        return $this->pattern;
    }
}
