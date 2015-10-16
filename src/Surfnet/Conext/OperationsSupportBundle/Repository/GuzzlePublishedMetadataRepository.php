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

namespace Surfnet\Conext\OperationsSupportBundle\Repository;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadata;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadataFactory;

final class GuzzlePublishedMetadataRepository implements PublishedMetadataRepository
{
    /**
     * @var PublishedMetadataUrlRepository
     */
    private $publishedMetadataUrlRepository;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PublishedMetadataUrlRepository $publishedMetadataUrlRepository,
        ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->publishedMetadataUrlRepository = $publishedMetadataUrlRepository;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function getMetadataFor(Entity $entity)
    {
        try {
            return $this->unsafelyGetMetadataFor($entity);
        } catch (InvalidArgumentException $e) {
            $this->logger->error(sprintf('Metadata invalid: "%s"', $e->getMessage()), ['exception' => $e]);

            return null;
        }
    }

    /**
     * @param Entity $entity
     * @return null|PublishedMetadata
     */
    private function unsafelyGetMetadataFor(Entity $entity)
    {
        $this->logger->info(sprintf('Attempting to fetch published metadata for entity "%s"', $entity));

        $publishedMetadataUrl = $this->publishedMetadataUrlRepository->getPublishedMetadataUrlFor($entity);

        if ($publishedMetadataUrl === null) {
            $this->logger->info(
                sprintf(
                    'Configured metadata for entity "%s" has no published metadata URL configured, returning NULL',
                    $entity
                )
            );

            return null;
        }

        $response = $this->httpClient->request('GET', $publishedMetadataUrl, ['http_error' => false]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->info(
                sprintf('Published metadata HTTP response code was %d, returning NULL', $response->getStatusCode())
            );

            return null;
        }

        $xmlString = $response->getBody()->getContents();
        $xml = $this->loadXml($xmlString, $entity);

        $metadatas = PublishedMetadataFactory::fromMetadataXml($xml);
        $this->logger->info(sprintf('Published metadata contains %d entities', count($metadatas)));

        $metadatas = $metadatas->findByEntity($entity);
        $this->logger->info(
            sprintf('Published metadata contains %d entries for entity "%s"', count($metadatas), $entity)
        );

        switch (count($metadatas)) {
            case 0:
                return null;
            case 1:
                return $metadatas->first();
            default:
                $this->logger->warning('Multiple metadata entries found for entity, returning NULL');

                return null;
        }
    }

    /**
     * @param string $xmlString
     * @param Entity $entity
     * @return null|SimpleXMLElement
     */
    private function loadXml($xmlString, Entity $entity)
    {
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $xml = simplexml_load_string($xmlString);
            $xmlErrors = libxml_get_errors();
        } finally {
            libxml_use_internal_errors($previousUseInternalErrors);
            libxml_clear_errors();
        }

        if (count($xmlErrors) === 0) {
            return $xml;

        }

        $this->logger->info(
            sprintf(
                'Published metadata for entity "%s" contains XML errors: %s',
                $entity,
                $this->formatLibXmlErrors($xmlErrors)
            )
        );

        return null;
    }

    /**
     * @param object[] $xmlErrors
     * @return string
     */
    private function formatLibXmlErrors(array $xmlErrors)
    {
        return join(
            ', ',
            array_map(
                function ($error) {
                    return sprintf("(%d:%d) %s", $error->line, $error->column, $error->message);
                },
                $xmlErrors
            )
        );
    }
}
