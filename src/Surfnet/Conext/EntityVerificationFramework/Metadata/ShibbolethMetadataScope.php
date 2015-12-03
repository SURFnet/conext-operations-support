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

final class ShibbolethMetadataScope implements ConfiguredMetadataValidatable
{
    /**
     * @var string|null
     */
    private $literal;

    /**
     * @var RegularExpression|null
     */
    private $regexp;

    /**
     * @param string $literal
     * @return ShibbolethMetadataScope
     */
    public static function literal($literal)
    {
        Assert::string($literal, 'Scope value "%s" must be a string, got "%s"');

        $scope = new ShibbolethMetadataScope();
        $scope->literal = $literal;

        return $scope;
    }

    /**
     * @param RegularExpression $regexp
     * @return ShibbolethMetadataScope
     */
    public static function regexp(RegularExpression $regexp)
    {
        $scope = new ShibbolethMetadataScope();
        $scope->regexp = $regexp;

        return $scope;
    }

    /**
     * @param mixed $data Array structure representing ShibbolethMetadataScope
     * @param string $propertyPath
     * @return ShibbolethMetadataScope
     */
    public static function deserialize($data, $propertyPath)
    {
        Assert::keyExists($data, 'allowed', 'ShibbolethMetadataScope doesn\'t contain key "%s"', $propertyPath);
        Assert::string($data['allowed'], null, $propertyPath . '.allowed');

        $regexp = false;
        if (array_key_exists('regexp', $data)) {
            Assert::boolean($data['regexp'], null, $propertyPath . '.regexp');
            $regexp = $data['regexp'];
        }

        if ($regexp) {
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
                $validator->addViolation('Literal ShibbolethMetadataScope may not be blank');
            }

            return;
        }

        $validator->validate($this->regexp, $context);
    }

    /**
     * @param ShibbolethMetadataScope $other
     * @return bool
     */
    public function equals(ShibbolethMetadataScope $other)
    {
        return ($this->literal && $this->literal === $other->literal)
            || ($this->regexp && $other->regexp && $this->regexp->equals($other->regexp));
    }

    public function __toString()
    {
        if ($this->literal !== null) {
            return sprintf('ShibbolethMetadataScope(literal=%s)', $this->literal);
        } else {
            return sprintf('ShibbolethMetadataScope(regexp=%s)', $this->regexp);
        }
    }
}
