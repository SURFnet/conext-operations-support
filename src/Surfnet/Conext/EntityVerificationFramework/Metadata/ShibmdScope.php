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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class ShibmdScope implements ConfiguredMetadataValidatable
{
    /**
     * @var string
     */
    private $literal;

    /**
     * @var RegularExpression
     */
    private $regexp;

    /**
     * @param string $literal
     * @return ShibmdScope
     */
    public static function literal($literal)
    {
        Assert::string($literal, 'Scope value "%s" must be a string, got "%s"');

        $scope = new ShibmdScope();
        $scope->literal = $literal;

        return $scope;
    }

    /**
     * @param RegularExpression $regexp
     * @return ShibmdScope
     */
    public static function regexp(RegularExpression $regexp)
    {
        $scope = new ShibmdScope();
        $scope->regexp = $regexp;

        return $scope;
    }

    /**
     * @param mixed $data Array structure representing Shibmd scope
     * @param string $propertyPath
     * @return ShibmdScope
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::keyExists($data, 'allowed', 'Shibmd scope doesn\'t contain key "%s"', $propertyPath . '.allowed');
        Assert::keyExists($data, 'regexp', 'Shibmd scope doesn\'t contain key "%s"', $propertyPath . '.regexp');
        Assert::string($data['allowed'], null, $propertyPath . '.allowed');
        Assert::boolean($data['regexp'], null, $propertyPath . '.regexp');

        if ($data['regexp']) {
            return self::regexp(new RegularExpression('~' . $data['allowed'] . '~'));
        } else {
            return self::literal($data['allowed']);
        }
    }

    private function __construct()
    {
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        if ($this->literal !== null) {
            if (trim($this->literal) === '') {
                $validator->addViolation('Literal Shibmd scope may not be blank');
            }

            return;
        }

        $validator->validate($this->regexp, $context);
    }

    public function __toString()
    {
        if ($this->literal !== null) {
            return sprintf('ShibmdScope(literal=%s)', $this->literal);
        } else {
            return sprintf('ShibmdScope(regexp=%s)', $this->regexp);
        }
    }
}
