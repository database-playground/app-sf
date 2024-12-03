<?php

declare(strict_types=1);

use App\Entity\Question;
use Symfony\Config\MeilisearchConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (MeilisearchConfig $meiliSearchConfig): void {
    $meiliSearchConfig
        ->url(env('MEILISEARCH_URL'))
        ->apiKey(env('MEILISEARCH_API_KEY'))
        ->indices()
            ->name('questions')
            ->class(Question::class)
            ->enableSerializerGroups(true)
            ->settings([
                'filterableAttributes' => [
                    'type',
                    'difficulty',
                ],
                'sortableAttributes' => [
                    'id',
                ],
            ]);
};
