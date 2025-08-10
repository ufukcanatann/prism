<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Core\View\AdvancedView;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(AdvancedView::class, function (Container $container) {
            return AdvancedView::getInstance($container);
        });
    }
}
