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

final class Url
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var mixed[]
     */
    private $parts;

    /**
     * @param string $data
     * @param string $propertyPath
     * @return Url
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::string($data, $propertyPath);

        $url = new self();

        if (filter_var($data, FILTER_VALIDATE_URL) === false) {
            $parts = false;
        } else {
            $parts = parse_url($data);
        }

        $isValid = $parts !== false;

        $url->url   = $data;
        $url->isValid = $isValid;
        $url->parts = ($parts ?: []) + [
            'scheme'   => null,
            'host'     => null,
            'port'     => null,
            'user'     => null,
            'pass'     => null,
            'path'     => null,
            'query'    => null,
            'fragment' => null,
        ];

        return $url;
    }

    /**
     * @param string $scheme
     * @return bool
     */
    public function isScheme($scheme)
    {
        Assert::string($scheme);

        return strtolower($this->parts['scheme']) === strtolower($scheme);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @param Url $other
     * @return bool
     */
    public function equals(Url $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->url;
    }
}
