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

/**
 * Wrapper type for some of the functions from the OpenSSL extension. Used only for testing purposes due to language
 * limitations.
 */
interface OpenSsl
{
    const X509_PARSE_SHORT_NAMES = true;
    const X509_PARSE_LONG_NAMES = false;
    const X509_FINGERPRINT_ALGO_MD5 = 'md5';
    const X509_FINGERPRINT_ALGO_SHA1 = 'sha1';
    const X509_FINGERPRINT_OUTPUT_RAW = true;
    const X509_FINGERPRINT_OUTPUT_HEX = false;

    /**
     * Parse an X509 certificate and return the information as an array.
     *
     * @param string x509Cert
     * @param bool $shortNames OPTIONAL
     * @return array|false
     * @see http://php.net/manual/en/function.openssl-x509-parse.php
     */
    public function x509Parse($x509Cert, $shortNames = true);

    /**
     * Calculates the fingerprint, or digest, of a given X.509 certificate.
     *
     * @param string $x509
     * @param string $hashAlgorithm OPTIONAL
     * @param bool   $rawOutput OPTIONAL
     * @return bool|string `false` on failure
     * @see http://php.net/manual/en/function.openssl-x509-fingerprint.php
     */
    public function x509Fingerprint(
        $x509,
        $hashAlgorithm = self::X509_FINGERPRINT_ALGO_SHA1,
        $rawOutput = self::X509_FINGERPRINT_OUTPUT_HEX
    );

    /**
     * Returns the last error from the OpenSSL library. Error messages are queued, so this function should be called
     * multiple times to collect all of the information. The last error will be the most recent one.
     *
     * @return string|false
     * @see http://php.net/manual/en/function.openssl-error-string.php
     */
    public function errorString();
}
