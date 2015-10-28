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

final class GuestQualifier
{
    const ALL = 'All';
    const SOME = 'Some';
    const NONE = 'None';

    /**
     * @var string
     */
    private $guestQualifier;

    /**
     * @param string $guestQualifier
     */
    public function __construct($guestQualifier)
    {
        Assert::choice(
            $guestQualifier,
            [self::ALL, self::SOME, self::NONE],
            'Guest qualifier must be one of "All", "Some", "None"'
        );

        $this->guestQualifier = $guestQualifier;
    }

    /**
     * @return bool
     */
    public function isAll()
    {
        return $this->guestQualifier === self::ALL;
    }

    /**
     * @return bool
     */
    public function isSome()
    {
        return $this->guestQualifier === self::SOME;
    }

    /**
     * @return bool
     */
    public function isNone()
    {
        return $this->guestQualifier === self::NONE;
    }

    /**
     * @param GuestQualifier $other
     * @return bool
     */
    public function equals(GuestQualifier $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return sprintf('GuestQualifier(%s)', $this->guestQualifier);
    }
}
