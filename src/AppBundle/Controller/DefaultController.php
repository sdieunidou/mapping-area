<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route(path="/", name="homepage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homepageAction()
    {
        $engines = $this->getDoctrine()->getRepository('AppBundle:Engine')->findAll();

        return $this->render('AppBundle:Default:homepage.html.twig', ['engines' => $engines]);
    }
}
