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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Surfnet\Conext\EntityVerificationFramework\Assert;

final class PemEncodedX509CertificateList implements IteratorAggregate, Countable
{
    /**
     * @var PemEncodedX509Certificate[]
     */
    private $certs;

    public function __construct(array $certs = [])
    {
        Assert::allIsInstanceOf($certs, PemEncodedX509Certificate::class);

        $this->certs = $certs;
    }

    /**
     * @param PemEncodedX509Certificate $cert
     * @return PemEncodedX509CertificateList
     */
    public function add(PemEncodedX509Certificate $cert)
    {
        return new PemEncodedX509CertificateList(array_merge($this->certs, [$cert]));
    }

    public function getIterator()
    {
        return new ArrayIterator($this->certs);
    }

    public function count()
    {
        return count($this->certs);
    }

    public function __toString()
    {
        return sprintf('PemEncodedX509CertificateList(%s)', join(', ', array_map('strval', $this->certs)));
    }
}
