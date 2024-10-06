<?php

declare(strict_types=1);

namespace App\Entity\Form;

class MetadataDto
{
    /**
     * @var array<string, string|null>
     */
    public array $metadata;

    /**
     * @return array<string, string|null>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, string|null> $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
