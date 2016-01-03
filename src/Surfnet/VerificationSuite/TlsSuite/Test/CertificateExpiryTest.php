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

namespace Surfnet\VerificationSuite\TlsSuite\Test;

use DateInterval;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\AssertionConsumerService;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadata;
use Surfnet\Conext\EntityVerificationFramework\Metadata\SingleSignOnService;
use Surfnet\Conext\EntityVerificationFramework\TestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Host;
use Surfnet\Conext\EntityVerificationFramework\Value\HostSet;

final class CertificateExpiryTest implements VerificationTest
{
    public function verify(VerificationContext $verificationContext)
    {
        $logger     = $verificationContext->getLogger();
        $metadata   = $verificationContext->getConfiguredMetadata();
        $tlsService = $verificationContext->getTlsService();

        $severity = VerificationTestResult::SEVERITY_MEDIUM;
        $errors = [];

        $hosts = $this->getEntityServiceHostsFromMetadata($metadata);
        $logger->debug(sprintf('Entity services are hosted from %d TLS host(s)', count($hosts)));

        foreach ($hosts as $host) {
            /** @var Host $host */
            $result = $tlsService->getEndUserCertificateForHost($host);

            if ($result->didCertificateExtractionFail()) {
                $logger->error(
                    sprintf(
                        "Certificate could not be retrieved from entity service host %s:%s:\n%s",
                        $host->getHostname(),
                        $host->getPort(),
                        $result->getErrorMessage()
                    )
                );
                continue;
            }
            if ($result->didCertificateParsingFail()) {
                $logger->error(
                    sprintf(
                        "Certificate retrieved from entity service host %s:%s could not be parsed:\n" .
                        "%s",
                        $host->getHostname(),
                        $host->getPort(),
                        $result->getErrorMessage()
                    )
                );
                continue;
            }
            if (!$result->wasSuccessful()) {
                continue;
            }

            $certificate = $result->getCertificate();
            $logger->debug(sprintf('Checking expiration of %s', $certificate));

            if (!$certificate->isStillValid()) {
                $severity = max($severity, VerificationTestResult::SEVERITY_CRITICAL);
                $errors[] = sprintf(
                    'Certificate retrieved from entity service host %s:%s is no longer valid: ' .
                    'its valid-to date has expired',
                    $host->getHostname(),
                    $host->getPort()
                );
            } elseif (!$certificate->isStillValidFor(new DateInterval('P2W'))) {
                $severity = max($severity, VerificationTestResult::SEVERITY_HIGH);
                $errors[] = sprintf(
                    'Certificate retrieved from entity service host %s:%s will expire within two weeks',
                    $host->getHostname(),
                    $host->getPort()
                );
            } elseif (!$certificate->isStillValidFor(new DateInterval('P1M'))) {
                $severity = max($severity, VerificationTestResult::SEVERITY_MEDIUM);
                $errors[] = sprintf(
                    'Certificate retrieved from entity service host %s:%s will expire ' .
                    'within one month',
                    $host->getHostname(),
                    $host->getPort()
                );
            }
        }

        if (count($errors) === 0) {
            return TestResult::success();
        }

        return TestResult::failed(
            'One or more certificates are no longer valid or about to fail',
            ' * ' . join("\n * ", $errors),
            $severity
        );
    }

    public function shouldBeSkipped(VerificationContext $verificationContext)
    {
        return false;
    }

    public function getReasonToSkip()
    {
        throw new LogicException('Test is not skipped');
    }

    /**
     * @param ConfiguredMetadata $metadata
     * @return HostSet
     */
    private function getEntityServiceHostsFromMetadata(ConfiguredMetadata $metadata)
    {
        return new HostSet(
            array_merge(
                $metadata->getAssertionConsumerServices()
                    ->filter(function (AssertionConsumerService $acs) {
                        return $acs->isValid();
                    })->map(function (AssertionConsumerService $acs) {
                        $location = $acs->getLocation();
                        return new Host($location->getHostname(), $location->hasAPort() ? $location->getPort() : 443);
                    }),
                $metadata->getSingleSignOnServices()
                    ->filter(function (SingleSignOnService $sso) {
                        return $sso->isValid();
                    })->map(function (SingleSignOnService $sso) {
                        $location = $sso->getLocation();
                        return new Host($location->getHostname(), $location->hasAPort() ? $location->getPort() : 443);
                    })
            )
        );
    }
}
