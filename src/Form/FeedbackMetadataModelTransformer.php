<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Form\MetadataDto;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @implements DataTransformerInterface<array<string, string|null>, string>
 */
final readonly class FeedbackMetadataModelTransformer implements DataTransformerInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function transform(mixed $value): string
    {
        \assert(\is_array($value));

        return $this->serializer->serialize(
            (new MetadataDto())->setMetadata($value),
            'json'
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function reverseTransform(mixed $value): array
    {
        $deserialized = $this->serializer->deserialize($value, MetadataDto::class, 'json');

        return $deserialized->getMetadata();
    }
}
