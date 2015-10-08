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

final class EmailAddress
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @param string $data
     * @param string $propertyPath
     * @return EmailAddress
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::string($data, null, $propertyPath);

        $email = new EmailAddress();
        $email->emailAddress = $data;

        return $email;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param EmailAddress $other
     * @return bool
     */
    public function equals(EmailAddress $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->emailAddress;
    }
}
