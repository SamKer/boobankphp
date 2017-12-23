<?php

namespace SamKer\BoobankBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SamBoobankBundle:Default:index.html.twig');
    }
}
