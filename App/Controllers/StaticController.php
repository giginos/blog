<?php

namespace App\Controllers;

class StaticController extends AbstractController
{

    public function imprintAction()
    {
        return $this->render('imprint.html');
    }
}
