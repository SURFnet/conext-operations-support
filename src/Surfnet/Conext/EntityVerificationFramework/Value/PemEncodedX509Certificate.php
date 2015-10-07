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

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class PemEncodedX509Certificate
{
    /**
     * @var string
     */
    private $certificate;

    /**
     * @param string $data
     * @param string $propertyPath
     * @return PemEncodedX509Certificate
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::string($data, 'Certificate data must be a string', $propertyPath);

        $certificate = new PemEncodedX509Certificate();
        $certificate->certificate = $data;

        return $certificate;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return trim($this->certificate) !== '';
    }

    /**
     * @param PemEncodedX509Certificate $other
     * @return bool
     */
    public function equals(PemEncodedX509Certificate $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->certificate;
    }
}
