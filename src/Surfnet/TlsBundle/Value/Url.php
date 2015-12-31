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

namespace Surfnet\TlsBundle\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

class Url
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var mixed[]
     */
    private $parts;

    /**
     * @param string $string
     * @return Url
     */
    public static function fromString($string)
    {
        Assert::string($string, 'Value "%s" expected to be a URL, type %s given.');

        if (filter_var($string, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('URL must be valid, got "%s"', $string));
        }

        $parts = parse_url($string);
        if ($parts === false) {
            throw new InvalidArgumentException(
                sprintf('URL "%s" is malformed and could not be parsed into its constituent parts', $string)
            );
        }

        $url = new Url();
        $url->url   = $string;
        $url->parts = $parts + [
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

    final private function __construct()
    {
    }

    /**
     * Performs a case-insensitive comparison of this URL's scheme against the given scheme.
     *
     * @param string $scheme
     * @return bool
     */
    public function hasScheme($scheme)
    {
        Assert::string($scheme, 'Expected given scheme "%s" to be a string, got type "%s"');

        return strtolower($this->parts['scheme']) === strtolower($scheme);
    }

    /**
     * @param Url $other
     * @return bool
     */
    public function equals(Url $other)
    {
        return $this->url === $other->url;
    }

    /**
     * @return bool
     */
    public function hasAHostname()
    {
        return trim($this->parts['host']) !== '';
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        if (!$this->hasAHostname()) {
            throw new LogicException('This URL doesn\'t have a hostname');
        }

        return $this->parts['host'];
    }

    /**
     * @return bool
     */
    public function hasAPort()
    {
        return is_int($this->parts['port']);
    }

    /**
     * @return int
     */
    public function getPort()
    {
        if (!$this->hasAPort()) {
            throw new LogicException('This URL doesn\'t have a port');
        }

        return $this->parts['port'];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function __toString()
    {
        return $this->url;
    }
}
