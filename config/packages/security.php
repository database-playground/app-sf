<?php

declare(strict_types=1);

use App\Entity\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\Security\PasswordHasherConfig;
use Symfony\Config\SecurityConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator, SecurityConfig $securityConfig): void {
    $securityConfig->passwordHasher(PasswordAuthenticatedUserInterface::class, 'auto');

    // used to reload user from session & other features (e.g. switch_user)
    $securityConfig
        ->provider('app_user_provider')
            ->entity()
                ->class(User::class)
                ->property('email');

    $securityConfig
        ->firewall('dev')
            ->pattern('^/(_(profiler|wdt)|css|images|js)/')
            ->security(false);

    $mainFirewall = $securityConfig->firewall('main');

    $mainFirewall
        ->lazy(true)
        ->provider('app_user_provider');

    $mainFirewall
        ->formLogin()
            ->loginPath('app_login')
            ->checkPath('app_login')
            ->enableCsrf(true);

    $mainFirewall
        ->logout()
            ->path('app_logout')
            ->target('app_home');

    $mainFirewall
        ->rememberMe()
            ->secret(param('kernel.secret'))
            ->lifetime(604800 /* 1 week in seconds */);

    // https://symfony.com/doc/current/security/impersonating_user.html
    $mainFirewall->switchUser();

    // Allow anonymous access to the login form.
    $securityConfig
        ->accessControl()
            ->route('app_login')
            ->roles('PUBLIC_ACCESS');

    // Allow anonymous access to the feedback form.
    $securityConfig
        ->accessControl()
            ->route('app_feedback')
            ->roles('PUBLIC_ACCESS');

    // Admin
    $securityConfig
        ->accessControl()
            ->path('^/admin')
            ->roles('ROLE_ADMIN');

    // Others (for example, apps)
    $securityConfig
        ->accessControl()
            ->path('^/')
            ->roles('ROLE_USER');

    if ('test' === $containerConfigurator->env()) {
        $passwordHasher = $securityConfig->passwordHasher(PasswordAuthenticatedUserInterface::class);
        assert($passwordHasher instanceof PasswordHasherConfig);

        $passwordHasher
            ->algorithm('auto')
            ->cost(4)
            ->timeCost(3)
            ->memoryCost(10);
    }
};
