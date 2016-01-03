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

namespace Surfnet\TlsBundle\Parser;

use DateTimeImmutable;
use Surfnet\Conext\EntityVerificationFramework\Value\X509Certificate;
use Surfnet\TlsBundle\Assert;
use Surfnet\TlsBundle\OpenSsl\OpenSsl;
use Surfnet\TlsBundle\Parser\Exception\ParseException;

final class OpenSslX509CertificateParser implements X509CertificateParser
{
    const X509_PARSE_LONG_NAMES = false;

    /**
     * @var OpenSsl
     */
    private $openSsl;

    /**
     * @param OpenSsl $openSsl
     */
    public function __construct(OpenSsl $openSsl)
    {
        $this->openSsl = $openSsl;
    }

    public function parseString($string)
    {
        Assert::string($string, 'X509-encoded certificate must be a string');

        $certificateInfo = $this->parseCertificateInfo($string);

        return new X509Certificate($this->calculateFingerprint($string), $this->parseValidTo($certificateInfo));
    }

    /**
     * @param string $string
     * @return array
     */
    private function parseCertificateInfo($string)
    {
        $certificateInfo = $this->openSsl->x509Parse($string, self::X509_PARSE_LONG_NAMES);
        if ($certificateInfo === false) {
            throw new ParseException(
                sprintf(
                    'Given PEM-encoded X509 certificate cannot be parsed by OpenSSL: %s',
                    join('; ', $this->collectOpenSslErrors())
                )
            );
        }

        return $certificateInfo;
    }

    /**
     * @param array $certificateInfo
     * @return DateTimeImmutable
     */
    private function parseValidTo($certificateInfo)
    {
        if (!array_key_exists('validTo_time_t', $certificateInfo)) {
            throw new ParseException('Certificate contains no valid-to date');
        }
        $validToTimestamp = $certificateInfo['validTo_time_t'];
        if (!is_int($validToTimestamp)) {
            $type = is_object($validToTimestamp) ? get_class($validToTimestamp) : gettype($validToTimestamp);
            throw new ParseException(sprintf('Expected valid-to timestamp to be a string, got type "%s"', $type));
        }
        $validTo = new DateTimeImmutable('@' . $validToTimestamp);

        return $validTo;
    }

    /**
     * @param $string
     * @return bool|string
     */
    private function calculateFingerprint($string)
    {
        $fingerprint = $this->openSsl->x509Fingerprint($string);
        if ($fingerprint === false) {
            throw new ParseException(
                sprintf(
                    'Given PEM-encoded X509 certificate\'s fingerprint could not be calculated: %s',
                    join('; ', $this->collectOpenSslErrors())
                )
            );
        }

        return $fingerprint;
    }

    /**
     * @return string[]
     */
    private function collectOpenSslErrors()
    {
        $errors = [];
        while (true) {
            $error = $this->openSsl->errorString();
            if ($error === false) {
                break;
            }

            $errors[] = $error;
        }

        return $errors;
    }
}
