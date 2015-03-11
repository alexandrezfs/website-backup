<?php

namespace Wb\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WbBackendBundle:Default:index.html.twig', array('name' => $name));
    }
}
