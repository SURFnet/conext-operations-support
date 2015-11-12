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
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\PublishedMetadata;
use Surfnet\Conext\EntityVerificationFramework\Metadata\PublishedMetadataFactory;
use Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Xml\XmlHelper;

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

        $this->logger->info(sprintf('Published metadata URL is "%s"', $publishedMetadataUrl));

        try {
            $response = $this->httpClient->request(
                'GET',
                $publishedMetadataUrl,
                [RequestOptions::HTTP_ERRORS => false]
            );
        } catch (ConnectException $e) {
            $this->logger->info(
                sprintf('There was an error connecting to the metadata server: "%s"', $e->getMessage()),
                ['exception' => $e]
            );

            return null;
        } catch (RequestException $e) {
            $this->logger->info(
                sprintf('There was an error while communicating with the metadata server: "%s"', $e->getMessage()),
                ['exception' => $e]
            );

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->info(
                sprintf('Published metadata HTTP response code was %d, returning NULL', $response->getStatusCode())
            );

            return null;
        }

        $xmlString = $response->getBody()->getContents();
        $xml = XmlHelper::loadXml($xmlString, $this->logger);

        if ($xml === null) {
            return null;
        }

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
}
