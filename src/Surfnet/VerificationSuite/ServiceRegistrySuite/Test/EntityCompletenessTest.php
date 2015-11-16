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

namespace Surfnet\VerificationSuite\ServiceRegistrySuite\Test;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Contact;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Logo;
use Surfnet\Conext\EntityVerificationFramework\Metadata\NameIdFormat;
use Surfnet\Conext\EntityVerificationFramework\TestResult;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity) -- Not much to do about it. It tests a lot.
 * @SuppressWarnings(PHPMD.NPathComplexity)      -- Not much to do about it. It tests a lot.
 */
final class EntityCompletenessTest implements VerificationTest
{
    public function verify(VerificationContext $verificationContext)
    {
        $metadata = $verificationContext->getConfiguredMetadata();
        $notes = [];

        if (!$metadata->hasName()) {
            $notes[] = 'No name configured';
        } elseif ($metadata->getName()->hasFilledTranslationForLocale('en')) {
            $notes[] = 'No English name configured';
        } elseif ($metadata->getName()->hasFilledTranslationForLocale('nl')) {
            $notes[] = 'No Dutch name configured';
        }

        if (!$metadata->hasDescription()) {
            $notes[] = 'No description configured';
        } elseif ($metadata->getDescription()->hasFilledTranslationForLocale('en')) {
            $notes[] = 'No English description configured';
        } elseif ($metadata->getDescription()->hasFilledTranslationForLocale('nl')) {
            $notes[] = 'No Dutch description configured';
        }

        foreach ($metadata->getContacts() as $contact) {
            /** @var Contact $contact */
            if (!$contact->hasContactType()) {
                $notes[] = sprintf('%s has no contact type', $contact);
            } elseif (!$contact->hasValidContactType()) {
                $notes[] = sprintf(
                    '%s has invalid contact type, must be one of "support", "administrative", "technical"',
                    $contact
                );
            }
            if (!$contact->hasGivenName()) {
                $notes[] = sprintf('%s has no given name defined', $contact);
            } elseif (!$contact->hasFilledGivenName()) {
                $notes[] = sprintf('%s has empty given name', $contact);
            }
            if (!$contact->hasSurName()) {
                $notes[] = sprintf('%s has no surname defined', $contact);
            } elseif (!$contact->hasFilledSurName()) {
                $notes[] = sprintf('%s has empty surname', $contact);
            }
            if (!$contact->hasEmailAddress()) {
                $notes[] = sprintf('%s has no email address defined', $contact);
            } elseif (!$contact->hasValidEmailAddress()) {
                $notes[] = sprintf('%s has an invalid e-mail address', $contact);
            }
        }

        foreach ($metadata->getLogos() as $logo) {
            /** @var Logo $logo */
            if (!$logo->hasUrl()) {
                $notes[] = sprintf('Logo has no URL');
                continue;
            }
            if (!$logo->getUrl()->matches('~^https://static\.surfconext\.nl/logos/idp/.+\.png$~')) {
                $notes[] = sprintf(
                    'Logo %s URL does not match https://static.surfconext.nl/logos/idp/<name>.png',
                    $logo->getUrl()
                );
            }

            $response = $verificationContext->getHttpClient()->request('GET', $logo->getUrl());
            if ($response->getStatusCode() !== 200) {
                $notes[] = sprintf(
                    'Logo %s is not available, server returned status code %d',
                    $logo->getUrl(),
                    $response->getStatusCode()
                );
            }

            if (!$logo->isWidthValid()) {
                $notes[] = sprintf('Logo width is invalid: %s', $logo);
            }
            if (!$logo->isHeightValid()) {
                $notes[] = sprintf('Logo height is invalid: %s', $logo);
            }
        }

        if (!$metadata->hasSignRedirectsConfigured()) {
            $notes[] = 'The sign redirects option is not configured to be enabled or disabled';
        }

        if (!$metadata->hasDefaultNameIdFormat()) {
            $notes[] = 'No default NameIDFormat is configured';
        } elseif (!$metadata->getDefaultNameIdFormat()->isValidFormat()) {
            $notes[] = sprintf(
                'Default NameIDFormat is "%s", must be one of "%s"',
                $metadata->getDefaultNameIdFormat(),
                join('", "', NameIdFormat::VALID_FORMATS)
            );
        }

        if (count($notes) > 0) {
            $notesString = " * " . join("\n * ", $notes);
            return TestResult::failed('Entity incomplete', $notesString, TestResult::SEVERITY_MEDIUM);
        }

        return TestResult::success();
    }

    public function shouldBeSkipped(VerificationContext $verificationContext)
    {
        return false;
    }

    public function getReasonToSkip()
    {
        throw new LogicException('Test is not skipped');
    }
}
