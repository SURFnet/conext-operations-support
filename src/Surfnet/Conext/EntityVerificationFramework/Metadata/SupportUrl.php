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

use GuzzleHttp\Exception\ConnectException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathConstraintViolationWriter;
use Symfony\Component\HttpFoundation\Response;

final class SupportUrl extends MultiLocaleUrl implements ConfiguredMetadataValidatable
{
    private static $requiredLocales = ['en', 'nl'];

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        $urls = $this->getUrls();
        $locales = array_keys($urls);

        if (array_diff($locales, self::$requiredLocales) !== array_diff(self::$requiredLocales, $locales)) {
            $violations->add(
                sprintf(
                    'Support URL must have locales "%s" configured, has "%s"',
                    join('","', self::$requiredLocales),
                    join('","', $locales)
                )
            );
        }

        foreach ($urls as $url) {
            $visitor->visit($url, new SubpathConstraintViolationWriter($violations, 'Support URL'), $context);

            if (!$url->isValid()) {
                continue;
            }

            try {
                $response = $context->getHttpClient()->request('GET', $url->getValidUrl());
            } catch (ConnectException $e) {
                $violations->add(
                    sprintf(
                        'An error occurred while connecting to support URL "%s": "%s"',
                        $url->getValidUrl(),
                        $e->getMessage()
                    )
                );

                continue;
            }
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $violations->add(sprintf(
                    'Support URL is not available ("%s"), server returned status code %d',
                    $url->getValidUrl(),
                    $response->getStatusCode()
                ));
            }
        }
    }
}
