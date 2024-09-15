<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

interface Importable
{
    public static function fromJsonObject(object $json): self;
}