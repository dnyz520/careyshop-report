<?php

namespace careyshop;

use think\Route;
use think\Service;

class Report extends Service
{
    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->get('report', function () {
                return 'Report';
            });
        });
    }
}
