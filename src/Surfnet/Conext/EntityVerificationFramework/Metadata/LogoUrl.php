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

use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;
use Symfony\Component\HttpFoundation\Response;

final class LogoUrl extends Url
{
    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        parent::validate($visitor, $violations, $context);

        if (!$this->isValid()) {
            return;
        }

        if (!$this->matches('~^https://static\.surfconext\.nl/.+\\..+$~')) {
            $violations->add(
                sprintf(
                    'Logo URL "%s" does not match https://static.surfconext.nl/*.*',
                    $this
                )
            );
        }

        $response = $context->getHttpClient()->request('GET', $this->getValidUrl());
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $violations->add(sprintf(
                'Logo "%s" is not available, server returned status code %d',
                $this,
                $response->getStatusCode()
            ));
        }
    }
}
