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

namespace Surfnet\TlsBundle\OpenSsl;

use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Value\Host;
use Surfnet\TlsBundle\OpenSsl\Client\GetEndUserCertificateResult;
use Symfony\Component\Process\ProcessBuilder;

final class CliClient implements Client
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getEndUserCertificateForHost(Host $host)
    {
        $this->logger->info(sprintf('Fetching end user certificate for %s using OpenSSL', $host));

        $this->logger->info(sprintf('Fetching connection information for %s', $host));
        $sClientProcess =
            ProcessBuilder::create(
                ['openssl', 's_client', '-connect', $host->getHostname() . ':' . $host->getPort()]
            )
            ->setInput('')
            ->getProcess();
        $exitCode = $sClientProcess->run();

        if ($exitCode !== 0) {
            $this->logger->info(sprintf('Connection to SSL endpoint failed: %s', $sClientProcess->getErrorOutput()));

            return GetEndUserCertificateResult::connectionFailed(
                sprintf('Connection to SSL endpoint failed: %s', $sClientProcess->getErrorOutput())
            );
        }

        $this->logger->info('Extracting certificate from connection information');
        $x509Process = ProcessBuilder::create(['openssl', 'x509', '-inform', 'pem', '-outform', 'pem'])
            ->setInput($sClientProcess->getOutput())
            ->getProcess();
        $exitCode = $x509Process->run();

        if ($exitCode !== 0) {
            $this->logger->info(
                sprintf(
                    'Extraction of certificate information from diagnostics failed: %s',
                    $x509Process->getErrorOutput()
                )
            );

            return GetEndUserCertificateResult::certificateExtractionFailed(
                sprintf(
                    'Extraction of certificate information from diagnostics failed: %s',
                    $x509Process->getErrorOutput()
                )
            );
        }

        $this->logger->info('Certificate successfully extracted using OpenSSL');

        return GetEndUserCertificateResult::success($x509Process->getOutput());
    }
}
