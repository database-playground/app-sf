<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig
        ->form()
        ->csrfProtection()
        ->tokenId('submit')
    ;

    $frameworkConfig
        ->csrfProtection()
        ->statelessTokenIds(['submit', 'authenticate', 'logout'])
    ;
};
