<?php

namespace App\Services;

use App\Controllers\AdminController;
use App\Controllers\CategoryController;
use App\Controllers\DetailController;
use App\Controllers\ErrorController;
use App\Controllers\IndexController;
use App\Controllers\StaticController;
use Doctrine\ORM\EntityManagerInterface;

class RouterService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RouterService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function dispatch()
    {
        $action = 'index';

        if ($this->isHome()) {
            $controller = new IndexController($this->entityManager);
        }

        if (!isset($controller) && $this->isDetail()) {
            $controller = new DetailController($this->entityManager);
        }

        if (!isset($controller) && $this->isCategory()) {
            $controller = new CategoryController($this->entityManager);
        }

        if (!isset($controller) && $this->isStatic()) {
            $controller = new StaticController($this->entityManager);
            $action = $this->getActionByUrl('service');
        }

        if (!isset($controller) && $this->isAdmin()) {
            $controller = new AdminController($this->entityManager);
            $action = $this->getActionByUrl('admin');
        }

        if (!isset($controller)) {
            $controller = new ErrorController($this->entityManager);
            $action = 'Error404';
        }

        return $controller->{$action . 'Action'}();
    }

    private function isHome() : bool
    {
        return $_SERVER['REQUEST_URI'] == '' || $_SERVER['REQUEST_URI'] == '/' ? true : false;
    }

    private function isDetail() : bool
    {
        return substr($_SERVER['REQUEST_URI'], -5) == '.html' ? true : false;
    }

    private function isCategory() : bool
    {
        return false;
    }

    private function isStatic() : bool
    {
        return substr($_SERVER['REQUEST_URI'], 0, 9) == '/service/' ? true : false;
    }

    private function isAdmin() : bool
    {
        return substr($_SERVER['REQUEST_URI'], 0, 7) == '/admin/' ? true : false;
    }

    private function getActionByUrl(string $pattern) : string
    {
        $url = explode('/' . $pattern . '/', $_SERVER['REQUEST_URI']);
        $rawAction = array_pop($url);

        $actionParts = explode('?', $rawAction);
        $action = array_shift($actionParts);

        return strlen($action) > 0 ? $action : 'index';
    }
}
