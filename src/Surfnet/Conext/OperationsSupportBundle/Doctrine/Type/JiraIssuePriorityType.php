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

namespace Surfnet\Conext\OperationsSupportBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;

final class JiraIssuePriorityType extends Type
{
    const NAME = 'ops_jira_issue_priority';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof JiraIssuePriority) {
            throw new ConversionException(
                sprintf(
                    "Encountered illegal JIRA issue priority of type %s '%s', expected an JiraIssuePriority instance",
                    is_object($value) ? get_class($value) : gettype($value),
                    is_scalar($value) ? (string) $value : ''
                )
            );
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            return new JiraIssuePriority($value);
        } catch (InvalidArgumentException $e) {
            $doctrineExceptionMessage = ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            )->getMessage();

            throw new ConversionException($doctrineExceptionMessage, 0, $e);
        }
    }

    public function getName()
    {
        return self::NAME;
    }
}
