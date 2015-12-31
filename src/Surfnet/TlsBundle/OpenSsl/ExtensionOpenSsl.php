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

final class ExtensionOpenSsl implements OpenSsl
{
    public function x509Parse($x509Cert, $shortNames = true)
    {
        return openssl_x509_parse($x509Cert, $shortNames);
    }

    public function x509Fingerprint(
        $x509,
        $hashAlgorithm = self::X509_FINGERPRINT_ALGO_SHA1,
        $rawOutput = self::X509_FINGERPRINT_OUTPUT_HEX
    ) {
        return openssl_x509_fingerprint($x509, $hashAlgorithm, $rawOutput);
    }

    public function errorString()
    {
        return openssl_error_string();
    }
}
