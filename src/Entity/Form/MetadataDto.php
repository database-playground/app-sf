<?php

declare(strict_types=1);

namespace App\Entity\Form;

class MetadataDto
{
    /**
     * @var array<string, null|string>
     */
    public array $metadata;

    /**
     * @return array<string, null|string>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, null|string> $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
