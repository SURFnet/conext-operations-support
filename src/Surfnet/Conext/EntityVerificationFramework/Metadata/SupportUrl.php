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
use Symfony\Component\HttpFoundation\Response;

final class SupportUrl extends MultiLocaleUrl implements ConfiguredMetadataValidatable
{
    private static $requiredLocales = ['en', 'nl'];

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        $urls = $this->getUrls();
        $locales = array_keys($urls);

        if (array_diff($locales, self::$requiredLocales) !== array_diff(self::$requiredLocales, $locales)) {
            $validator->addViolation(
                sprintf(
                    'Support URL must have locales "%s" configured, has "%s"',
                    join('","', self::$requiredLocales),
                    join('","', $locales)
                )
            );
        }

        foreach ($urls as $url) {
            $validator->validate($url, $context);

            if (!$url->isValid()) {
                continue;
            }

            $response = $context->getHttpClient()->request('GET', $url->getValidUrl());
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $validator->addViolation(sprintf(
                    'Support URL is not available ("%s"), server returned status code %d',
                    $url->getValidUrl(),
                    $response->getStatusCode()
                ));
            }
        }
    }
}
