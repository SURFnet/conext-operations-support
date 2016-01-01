<?php

/**
 * Copyright 2016 SURFnet B.V.
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

final class Host
{
    /**
     * @var string
     */
    private $hostname;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $hostname
     * @param int    $port
     */
    public function __construct($hostname, $port)
    {
        Assert::string($hostname, 'Hostname "%s" must be a string, got type "%s"');
        Assert::notBlank($hostname, 'Hostname may not be empty');
        Assert::integer($port, 'Port "%s" must be a string, got type "%s"');

        $this->hostname = $hostname;
        $this->port     = $port;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param Host $other
     * @return bool
     */
    public function equals(Host $other)
    {
        return $this->hostname === $other->hostname && $this->port === $other->port;
    }

    public function __toString()
    {
        return sprintf('Host(hostname="%s", port=%d)', $this->hostname, $this->port);
    }
}
