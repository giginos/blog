<?php

namespace Tests;

use App\Services\RouterService;

class RouterServiceTest extends UnitTest
{
    public function testDispatch()
    {
        // Check for indexController
        $routerService = new RouterService($this->entityManager);
        $res = $routerService->dispatch();
        $this->assertEquals('index.html', $res['template']);
        $this->assertArrayHasKey('view', $res);

        // Check for adminController
        $_SERVER['REQUEST_URI'] = '/admin/listImages';
        $routerService = new RouterService($this->entityManager);
        $res = $routerService->dispatch();
        $this->assertEquals('admin/listImages.html', $res['template']);
        $this->assertArrayHasKey('view', $res);

        // Check for staticController
        $_SERVER['REQUEST_URI'] = '/service/imprint';
        $routerService = new RouterService($this->entityManager);
        $res = $routerService->dispatch();
        $this->assertEquals('imprint.html', $res['template']);
        $this->assertArrayHasKey('view', $res);
    }
}
