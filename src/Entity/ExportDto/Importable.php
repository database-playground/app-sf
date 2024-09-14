<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

interface Importable
{
    public static function fromJsonObject(\stdClass $json): self;
}
