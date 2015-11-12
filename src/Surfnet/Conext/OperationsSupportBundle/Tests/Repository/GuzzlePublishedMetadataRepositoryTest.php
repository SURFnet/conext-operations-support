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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Repository;

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadata;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadataFactory;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadataList;
use Surfnet\Conext\OperationsSupportBundle\Repository\GuzzlePublishedMetadataRepository;
use Surfnet\Conext\OperationsSupportBundle\Repository\PublishedMetadataUrlRepository;
use Surfnet\Conext\OperationsSupportBundle\Xml\XmlHelper;

class GuzzlePublishedMetadataRepositoryTest extends TestCase
{
    /**
     * I'm sorry you have to see this most horrible test. It demonstrates how complex GuzzlePublishedMetadataRepository
     * is. Since hours are scarce, its complexity has been registered as technical debt
     * (https://www.pivotaltracker.com/story/show/105951230).
     *
     * @test
     * @group metadata
     * @runInSeparateProcess
     */
    public function it_can_fetch_metadata()
    {
        $entity = function () {
            return new Entity(new EntityId('meh'), EntityType::IdP());
        };
        $publishedMetadataUrl = 'https://metadata.invalid';

        /** @var PublishedMetadataUrlRepository|MockInterface $publishedMetadataUrlRepository */
        $publishedMetadataUrlRepository = m::mock(PublishedMetadataUrlRepository::class);
        $publishedMetadataUrlRepository
            ->shouldReceive('getPublishedMetadataUrlFor')
            ->once()
            ->with(self::voEquals($entity()))
            ->andReturn($publishedMetadataUrl);

        $logger = new NullLogger();

        $xml = '<?xml version="1.0" encoding="UTF-8"?><stub/>';
        $xmlResponse = $this->createHttpResponseMock($xml);

        $xmlElement = new SimpleXMLElement($xml);
        $xmlHelper = m::mock('alias:' . XmlHelper::class);
        $xmlHelper->shouldReceive('loadXml')->with($xml, $logger)->andReturn($xmlElement);

        $expectedPublishedMetadata = m::mock(PublishedMetadata::class);
        $expectedPublishedMetadata
            ->shouldReceive('isPublishedFor')
            ->with(self::voEquals($entity()))
            ->andReturn(true);
        $publishedMetadataList = new PublishedMetadataList([$expectedPublishedMetadata]);
        $publishedMetadataFactory = m::mock('alias:' . PublishedMetadataFactory::class);
        $publishedMetadataFactory->shouldReceive('fromMetadataXml')->with($xmlElement)->andReturn($publishedMetadataList);

        /** @var ClientInterface|MockInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient
            ->shouldReceive('request')
            ->once()
            ->with('GET', $publishedMetadataUrl, m::any())
            ->andReturn($xmlResponse);

        $repository = new GuzzlePublishedMetadataRepository(
            $publishedMetadataUrlRepository,
            $httpClient,
            $logger
        );
        $actualPublishedMetadata = $repository->getMetadataFor($entity());

        $this->assertSame($expectedPublishedMetadata, $actualPublishedMetadata);
    }

    /**
     * @param object $expectedValueObject
     * @return callable
     */
    private static function voEquals($expectedValueObject)
    {
        return m::on(
            function ($actualValueObject) use ($expectedValueObject) {
                return $actualValueObject->equals($expectedValueObject);
            }
        );
    }

    /**
     * @param string $xml
     * @return HttpResponseInterface|MockInterface
     */
    private function createHttpResponseMock($xml)
    {
        $xmlStream = m::mock(StreamInterface::class);
        $xmlStream->shouldReceive('getContents')->andReturn($xml);
        $xmlResponse = m::mock(HttpResponseInterface::class);
        $xmlResponse->shouldReceive('getStatusCode')->andReturn(200);
        $xmlResponse->shouldReceive('getBody')->andReturn($xmlStream);

        return $xmlResponse;
    }
}
