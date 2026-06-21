<?php

namespace App\Contracts;

use App\DTO\MovieApiResult;

interface MovieApiProviderContract
{
    public function findByExternalId(string $externalId): MovieApiResult;

    public function searchByTitle(string $title): MovieApiResult;

    public function providerName(): string;
}
