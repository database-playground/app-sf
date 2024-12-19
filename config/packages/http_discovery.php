<?php

declare(strict_types=1);

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->alias(RequestFactoryInterface::class, Psr17Factory::class)
        ->alias(ResponseFactoryInterface::class, Psr17Factory::class)
        ->alias(ServerRequestFactoryInterface::class, Psr17Factory::class)
        ->alias(StreamFactoryInterface::class, Psr17Factory::class)
        ->alias(UploadedFileFactoryInterface::class, Psr17Factory::class)
        ->alias(UriFactoryInterface::class, Psr17Factory::class)
    ;
};
