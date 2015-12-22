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

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

final class EmailAddress implements ConfiguredMetadataValidatable
{
    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @return EmailAddress
     */
    public static function notSet()
    {
        return new EmailAddress();
    }

    /**
     * @param string $emailAddress
     * @return EmailAddress
     */
    public static function fromString($emailAddress)
    {
        Assert::string($emailAddress, 'E-mail address must at least be a string');

        $email = new EmailAddress();
        $email->emailAddress = $emailAddress;

        return $email;
    }

    private function __construct()
    {
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        if (!filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL) !== false) {
            $violations->add('Contact e-mail address is not valid');
        }
    }

    /**
     * @param EmailAddress $other
     * @return bool
     */
    public function equals(EmailAddress $other)
    {
        return $this->emailAddress === $other->emailAddress;
    }

    public function __toString()
    {
        return $this->emailAddress;
    }
}
