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

namespace Surfnet\VerificationSuite\PublishedMetadataAvailabilitySuite\Test;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\TestResult;

final class IsAvailableTest implements VerificationTest
{
    public function verify(VerificationContext $verificationContext)
    {
        if (!$verificationContext->getConfiguredMetadata()->hasPublishedMetadataUrl()) {
            return TestResult::failed(
                'Published metadata URL is not configured',
                'The URL where the entity\'s metadata is published is not configured',
                VerificationTestResult::SEVERITY_MEDIUM
            );
        }

        if (!$verificationContext->hasRemoteMetadata()) {
            return TestResult::failed(
                'Published metadata is not available',
                'The entity\'s published metadata could not be fetched from the URL in the configured metadata',
                VerificationTestResult::SEVERITY_MEDIUM
            );
        }

        return TestResult::success();
    }

    public function shouldBeSkipped(VerificationContext $verificationContext)
    {
        return false;
    }

    public function getReasonToSkip()
    {
        throw new LogicException('Test should not be skipped');
    }
}
