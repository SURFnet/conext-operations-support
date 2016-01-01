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

namespace Surfnet\TlsBundle\Service;

use Surfnet\Conext\EntityVerificationFramework\Service\Tls\GetEndUserCertificateResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Host;
use Surfnet\TlsBundle\OpenSsl\Client as OpenSslClient;
use Surfnet\TlsBundle\Parser\Exception\ParseException;
use Surfnet\TlsBundle\Parser\X509CertificateParser;

final class TlsService implements \Surfnet\Conext\EntityVerificationFramework\Service\TlsService
{
    /**
     * @var OpenSslClient
     */
    private $openSslClient;

    /**
     * @var X509CertificateParser
     */
    private $certificateParser;

    public function __construct(OpenSslClient $openSslClient, X509CertificateParser $certificateParser)
    {
        $this->openSslClient     = $openSslClient;
        $this->certificateParser = $certificateParser;
    }

    public function getEndUserCertificateForHost(Host $host)
    {
        $certificateResult = $this->openSslClient->getEndUserCertificateForHost($host);

        if ($certificateResult->didConnectionFail()) {
            return GetEndUserCertificateResult::connectionFailed($certificateResult->getErrorMessage());
        }
        if ($certificateResult->didCertificateExtractionFail()) {
            return GetEndUserCertificateResult::certificateExtractionFailed($certificateResult->getErrorMessage());
        }

        try {
            $certificate = $this->certificateParser->parseString($certificateResult->getCertificate());
        } catch (ParseException $e) {
            return GetEndUserCertificateResult::certificateParsingFailed($e->getMessage());
        }

        return GetEndUserCertificateResult::success($certificate);
    }
}
