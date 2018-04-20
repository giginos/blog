<?php

namespace App\Controllers;

use \Twig_Loader_Filesystem;
use \Twig_Environment;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var array
     */
    protected $view = [];
    /**
     * @var string
     */
    protected $template = 'index.html';

    private $twig = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $loader = new Twig_Loader_Filesystem(dirname(__FILE__) . '/../../App/Views');
        $this->twig = new Twig_Environment($loader, [
            'cache' => false,
            'cachex' => './App/Cache',
        ]);
    }

    /**
     * @param string|null $template
     *
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render($template = null)
    {
        if ($template !== null) {
            $this->template = $template;
        }
        echo $this->twig->render($this->template, $this->view);

        return ['template' => $this->template, 'view' => $this->view];
    }
}
