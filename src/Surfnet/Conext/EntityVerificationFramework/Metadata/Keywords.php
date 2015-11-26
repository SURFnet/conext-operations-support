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

use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class Keywords extends MultiLocaleString implements ConfiguredMetadataValidatable
{
    public function validate(
        ConfiguredMetadataValidator $validator,
        ConfiguredMetadataValidationContext $context
    ) {
        if (!$this->hasFilledTranslationForLocale('en')) {
            $validator->addViolation('No English keywords configured');
        }
        if (!$this->hasFilledTranslationForLocale('nl')) {
            $validator->addViolation('No Dutch keywords configured');
        }
    }
}
