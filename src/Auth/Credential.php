<?php
namespace Swango\Aliyun\Sts\Auth;
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
class Credential {
    private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    private $refreshDate;
    private $expiredDate;
    private $accessKeyId;
    private $accessSecret;
    private $securityToken;
    function __construct(string $accessKeyId, string $accessSecret) {
        $this->accessKeyId = $accessKeyId;
        $this->accessSecret = $accessSecret;
        $this->refreshDate = date($this->dateTimeFormat);
    }
    public function isExpired(): bool {
        if ($this->expiredDate == null) {
            return false;
        }
        if (strtotime($this->expiredDate) > \Time\now()) {
            return false;
        }
        return true;
    }
    public function getRefreshDate(): string {
        return $this->refreshDate;
    }
    public function getExpiredDate(): string {
        return $this->expiredDate;
    }
    public function setExpiredDate(int $expiredHours): int {
        if ($expiredHours > 0) {
            return $this->expiredDate = date($this->dateTimeFormat, strtotime("+" . $expiredHours . " hour"));
        }
    }
    public function getAccessKeyId(): string {
        return $this->accessKeyId;
    }
    public function setAccessKeyId(string $accessKeyId): void {
        $this->accessKeyId = $accessKeyId;
    }
    public function getAccessSecret(): string {
        return $this->accessSecret;
    }
    public function setAccessSecret(string $accessSecret): void {
        $this->accessSecret = $accessSecret;
    }
}