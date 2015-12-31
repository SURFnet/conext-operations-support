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

namespace Surfnet\Conext\EntityVerificationFramework\Value;

use DateInterval;
use DateTime as CoreDateTime;
use DateTimeImmutable;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\DateTime\DateTime;

final class X509Certificate
{
    const X509_PARSE_LONG_NAMES = false;

    /**
     * The SHA1 fingerprint of the certificate.
     *
     * @var string
     */
    private $fingerprint;

    /**
     * @var DateTimeImmutable
     */
    private $validTo;

    /**
     * @param string            $fingerprint
     * @param DateTimeImmutable $validTo
     */
    public function __construct($fingerprint, DateTimeImmutable $validTo)
    {
        Assert::string($fingerprint, 'Certificate fingerprint must be a string');

        $this->fingerprint = $fingerprint;
        $this->validTo     = $validTo;
    }

    /**
     * @param DateInterval $interval
     * @return bool
     */
    public function isStillValidFor(DateInterval $interval)
    {
        return DateTime::now()->add($interval) < $this->validTo;
    }

    /**
     * @return bool
     */
    public function isStillValid()
    {
        return DateTime::now() < $this->validTo;
    }

    /**
     * @param X509Certificate $other
     * @return bool
     */
    public function equals(X509Certificate $other)
    {
        return $this->fingerprint === $other->fingerprint;
    }

    public function __toString()
    {
        return sprintf(
            'Certificate(fingerprint="%s", validTo="%s")',
            $this->fingerprint,
            $this->validTo->format(CoreDateTime::ATOM)
        );
    }
}
