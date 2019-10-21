<?php
namespace Swango\Aliyun\Sts;
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * 'License'); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * 'AS IS' BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
abstract class AcsRequest {
    protected $version;
    protected $product;
    protected $actionName;
    protected $regionId;
    protected $acceptFormat;
    protected $method;
    protected $protocolType = 'http';
    protected $queryParameters = [];
    protected $headers = [];
    function __construct(string $product, string $version, string $actionName) {
        $this->headers['x-sdk-client'] = 'php/2.0.0';
        $this->product = $product;
        $this->version = $version;
        $this->actionName = $actionName;
    }
    abstract public function getFinalQuery(Auth\ISigner $iSigner, Auth\Credential $credential): array;
    public function getVersion(): string {
        return $this->version;
    }
    public function setVersion($version): void {
        $this->version = $version;
    }
    public function getProduct(): string {
        return $this->product;
    }
    public function setProduct($product): void {
        $this->product = $product;
    }
    public function getActionName(): string {
        return $this->actionName;
    }
    public function setActionName($actionName): void {
        $this->actionName = $actionName;
    }
    public function getAcceptFormat(): string {
        return $this->acceptFormat;
    }
    public function setAcceptFormat($acceptFormat): void {
        $this->acceptFormat = $acceptFormat;
    }
    public function getQueryParameters(): array {
        return $this->queryParameters;
    }
    public function getHeaders(): array {
        return $this->headers;
    }
    public function getMethod(): ?string {
        return $this->method;
    }
    public function setMethod($method): void {
        $this->method = $method;
    }
    public function getProtocol(): ?string {
        return $this->protocolType;
    }
    public function setProtocol($protocol): void {
        $this->protocolType = $protocol;
    }
    public function getRegionId(): ?string {
        return $this->regionId;
    }
    public function setRegionId($region): void {
        $this->regionId = $region;
    }
}