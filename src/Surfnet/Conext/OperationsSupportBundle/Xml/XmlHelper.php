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

namespace Surfnet\Conext\OperationsSupportBundle\Xml;

use Psr\Log\LoggerInterface;
use SimpleXMLElement;

final class XmlHelper
{
    /**
     * @param string          $xml
     * @param LoggerInterface $logger
     * @return SimpleXMLElement|null
     */
    public static function loadXml($xml, LoggerInterface $logger)
    {
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $xmlElement = simplexml_load_string($xml);
        $xmlErrors = libxml_get_errors();

        libxml_use_internal_errors($previousUseInternalErrors);
        libxml_clear_errors();

        if (count($xmlErrors) === 0) {
            return $xmlElement;
        }

        $logger->error(
            sprintf(
                'XML contains errors: %s',
                self::formatLibXmlErrors($xmlErrors)
            )
        );

        return null;
    }

    /**
     * @param object[] $xmlErrors
     * @return string
     */
    private static function formatLibXmlErrors(array $xmlErrors)
    {
        return join(
            ', ',
            array_map(
                function ($error) {
                    return sprintf('(%d:%d) %s', $error->line, $error->column, $error->message);
                },
                $xmlErrors
            )
        );
    }
}
