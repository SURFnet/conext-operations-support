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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\PublishedMetadataFactory;
use Surfnet\Conext\EntityVerificationFramework\Metadata\PublishedMetadataList;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

class PublishedMetadataFactoryTest extends TestCase
{
    /**
     * @test
     * @group metadata
     */
    public function it_can_parse_a_single_entity_descriptor()
    {
        $metadataXmlString = file_get_contents(__DIR__ . '/fixtures/metadata-idp-engineblock.xml');
        $metadataXml = simplexml_load_string($metadataXmlString);

        $list = PublishedMetadataFactory::fromMetadataXml($metadataXml);

        $this->assertInstanceOf(PublishedMetadataList::class, $list);
        $this->assertCount(1, $list);
    }

    /**
     * @test
     * @group metadata
     */
    public function it_can_parse_multiple_entity_descriptors()
    {
        $metadataXmlString = file_get_contents(__DIR__ . '/fixtures/metadata-multi-engineblock-onegini.xml');
        $metadataXml = simplexml_load_string($metadataXmlString);

        $list = PublishedMetadataFactory::fromMetadataXml($metadataXml);

        $this->assertInstanceOf(PublishedMetadataList::class, $list);
        $this->assertCount(2, $list);
    }

    /**
     * @test
     * @group metadata
     */
    public function it_can_find_by_entity()
    {
        $metadataXmlString = file_get_contents(__DIR__ . '/fixtures/metadata-multi-engineblock-onegini.xml');
        $metadataXml = simplexml_load_string($metadataXmlString);

        $onegini = new \Surfnet\Conext\EntityVerificationFramework\Value\Entity(new EntityId('https://www.onegini.me'), EntityType::IdP());

        $list = PublishedMetadataFactory::fromMetadataXml($metadataXml);
        $this->assertCount(2, $list);
        $this->assertCount(1, $list->findByEntity($onegini));
    }
}
