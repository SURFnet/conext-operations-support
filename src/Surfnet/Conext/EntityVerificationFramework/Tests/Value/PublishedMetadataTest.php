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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Value\PublishedMetadata;

class PublishedMetadataTest extends TestCase
{
    /**
     * @test
     * @group metadata
     */
    public function it_deserialises_engineblock_idp_metadata()
    {
        $metadataIdp = file_get_contents(__DIR__ . '/fixtures/metadata-idp-engineblock.xml');
        $metadataIdpXml = simplexml_load_string($metadataIdp);

        $metadata = PublishedMetadata::fromEntityDescriptorXml($metadataIdpXml);

        $this->assertInstanceOf(PublishedMetadata::class, $metadata);
    }

    /**
     * @test
     * @group metadata
     */
    public function it_deserialises_engineblock_sp_metadata()
    {
        $metadataSp = file_get_contents(__DIR__ . '/fixtures/metadata-sp-engineblock.xml');
        $metadataSpXml = simplexml_load_string($metadataSp);

        $metadata = PublishedMetadata::fromEntityDescriptorXml($metadataSpXml);

        $this->assertInstanceOf(PublishedMetadata::class, $metadata);
    }

    /**
     * @test
     * @group metadata
     */
    public function it_deserialises_onegini_idp_metadata()
    {
        $metadataIdp = file_get_contents(__DIR__ . '/fixtures/metadata-idp-onegini.xml');
        $metadataIdpXml = simplexml_load_string($metadataIdp);

        $metadata = PublishedMetadata::fromEntityDescriptorXml($metadataIdpXml);

        $this->assertInstanceOf(PublishedMetadata::class, $metadata);
    }
}
