<?php
namespace Swango\Aliyun\Sts;
use Swango\Aliyun\Sts\ {
    Auth\Credential,
    Auth\ShaHmac1Signer,
    Regions\EndpointProvider,
    Regions\ProductDomain,
    Regions\Endpoint
};

interface IClientProfileInterface {
    public function getSigner();
    public function getRegionId();
    public function getFormat();
    public function getCredential();
}
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
class Profile implements IClientProfileInterface {
    private static $profile;
    private static $endpoints;
    private static $credential;
    private static $regionId;
    private static $acceptFormat;
    private static $isigner;
    private static $iCredential;
    private function __construct($regionId, $credential) {
        self::$regionId = $regionId;
        self::$credential = $credential;
    }
    public static function getProfile(string $regionId, string $accessKeyId, string $accessSecret): self {
        $credential = new Credential($accessKeyId, $accessSecret);
        self::$profile = new Profile($regionId, $credential);
        return self::$profile;
    }
    public function getSigner(): Auth\ISigner {
        if (null == self::$isigner) {
            self::$isigner = new ShaHmac1Signer();
        }
        return self::$isigner;
    }
    public function getRegionId(): string {
        return self::$regionId;
    }
    public function getFormat(): string {
        return self::$acceptFormat;
    }
    public function getCredential(): Auth\Credential {
        if (null == self::$credential && null != self::$iCredential) {
            self::$credential = self::$iCredential;
        }
        return self::$credential;
    }
    public static function getEndpoints(): array {
        if (null == self::$endpoints) {
            self::$endpoints = EndpointProvider::getEndpoints();
        }
        return self::$endpoints;
    }
    public static function addEndpoint(string $endpointName, string $regionId, string $product, string $domain): void {
        if (null == self::$endpoints) {
            self::$endpoints = self::getEndpoints();
        }
        $endpoint = self::findEndpointByName($endpointName);
        if (null == $endpoint) {
            self::addEndpoint_($regionId, $product, $domain, $endpoint);
        } else {
            self::updateEndpoint($regionId, $product, $domain, $endpoint);
        }
    }
    public static function findEndpointByName(string $endpointName): string {
        foreach (self::$endpoints as $key=>$endpoint) {
            if ($endpoint->getName() == $endpointName) {
                return $endpoint;
            }
        }
    }
    private static function addEndpoint_(string $endpointName, string $regionId, string $product, string $domain): void {
        $endpoint = new Endpoint($endpointName, [
            $regionId
        ], [
            new ProductDomain($product, $domain)
        ]);
        self::$endpoints[] = $endpoint;
    }
    private static function updateEndpoint(string $regionId, string $product, string $domain, string $endpoint): void {
        $regionIds = $endpoint->getRegionIds();
        if (! in_array($regionId, $regionIds)) {
            array_push($regionIds, $regionId);
            $endpoint->setRegionIds($regionIds);
        }

        $productDomains = $endpoint->getProductDomains();
        if (null == self::findProductDomain($productDomains, $product, $domain)) {
            array_push($productDomains, new ProductDomain($product, $domain));
        }
        $endpoint->setProductDomains($productDomains);
    }
    private static function findProductDomain(array $productDomains, string $product, string $domain): ?string {
        foreach ($productDomains as $key=>$productDomain) {
            if ($productDomain->getProductName() == $product && $productDomain->getDomainName() == $domain) {
                return $productDomain;
            }
        }
        return null;
    }
}