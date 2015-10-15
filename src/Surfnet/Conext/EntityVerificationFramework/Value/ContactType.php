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

final class ContactType
{
    const TYPE_TECHNICAL = 'technical';
    const TYPE_ADMINISTRATIVE = 'administrative';
    const TYPE_SUPPORT = 'support';

    const VALID_TYPES = [self::TYPE_TECHNICAL, self::TYPE_ADMINISTRATIVE, self::TYPE_SUPPORT];

    /** @var mixed */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        Assert::string($type, 'Contact type must be string');

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return in_array($this->type, self::VALID_TYPES, true);
    }

    /**
     * @param ContactType $other
     * @return bool
     */
    public function equals(ContactType $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->type;
    }
}
