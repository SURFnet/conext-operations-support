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
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class RegularExpression implements ConfiguredMetadataValidatable
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        Assert::string($pattern, 'Regular expression "%s" must be a string, got "%s"');

        $this->pattern = $pattern;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        set_error_handler(function ($level, $message) use ($validator) {
            $validator->addViolation(sprintf(
                'An error would occur during execution of regular expression "%s": "%s"',
                $this->pattern,
                $message
            ));
        });
        preg_match($this->pattern, 'test');
        restore_error_handler();

        $message = $this->getLastPregErrorMessage($this->pattern);
        if (!$message) {
            return;
        }

        $validator->addViolation($message);
    }

    /**
     * @param RegularExpression $other
     * @return bool
     */
    public function equals(RegularExpression $other)
    {
        return $this->pattern === $other->pattern;
    }

    public function __toString()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern The pattern that may have caused an error.
     * @return null|string
     */
    private function getLastPregErrorMessage($pattern)
    {
        $error = preg_last_error();

        switch ($error) {
            case PREG_NO_ERROR:
                return null;
            case PREG_INTERNAL_ERROR:
                return sprintf('Regular expression "%s" would cause an internal preg error', $pattern);
            case PREG_BACKTRACK_LIMIT_ERROR:
                return sprintf('Regular expression "%s" would cause a backtrack limit error', $pattern);
            case PREG_RECURSION_LIMIT_ERROR:
                return sprintf('Regular expression "%s" would cause a recursion limit error', $pattern);
            case PREG_BAD_UTF8_ERROR:
                return sprintf('Regular expression "%s" would cause a bad UTF-8 error', $pattern);
            case PREG_BAD_UTF8_OFFSET_ERROR:
                return sprintf('Regular expression "%s" would cause a bad UTF-8 offset error', $pattern);
        }

        if (defined('PREG_JIT_STACKLIMIT_ERROR') && $error === PREG_JIT_STACKLIMIT_ERROR) {
            return sprintf('Regular expression "%s" would cause a JIT stack limit error', $pattern);
        }

        throw new LogicException(
            sprintf('An unknown error occurred (%d) during execution of a Perl-compatible regular expression', $error)
        );
    }
}
