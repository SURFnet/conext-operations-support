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

namespace Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata;

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class ConstraintViolationList implements
    ConfiguredMetadataConstraintViolationReader,
    ConfiguredMetadataConstraintViolationWriter
{
    /**
     * @var string[]
     */
    private $violations;

    public function add($violation)
    {
        Assert::string($violation, 'Violation message must be a string');

        $this->violations[] = $violation;
    }

    public function all()
    {
        return $this->violations;
    }

    public function count()
    {
        return count($this->violations);
    }
}
