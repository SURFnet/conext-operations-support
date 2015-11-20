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

namespace Surfnet\Conext\EntityVerificationFramework\Metadata\Validator;

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class ConfiguredMetadataValidator implements ConfiguredMetadataValidatorInterface
{
    /**
     * @var string[]
     */
    private $violations = [];

    /**
     * @param ConfiguredMetadataValidatable       $validatable
     * @param ConfiguredMetadataValidationContext $context
     * @return void
     */
    public function validate(ConfiguredMetadataValidatable $validatable, ConfiguredMetadataValidationContext $context)
    {
        $validatable->validate($this, $context);
    }

    public function addViolation($violation)
    {
        Assert::string($violation, 'Constraint violation message must be a string');

        $this->violations[] = $violation;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
