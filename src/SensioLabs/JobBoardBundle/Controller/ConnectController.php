<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConnectController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        return $this->get('security.authentication.entry_point.sensiolabs_connect')->start($request);
    }

    /**
     * @Route("/sln_customizer.js", name="sln_customizer")
     * @Template("SensioLabsJobBoardBundle:Connect:customizer.js.twig")
     */
    public function customizationAction()
    {
        return array();
    }

    /**
     * @Route("/session/callback", name="session_callback")
     */
    public function sessionCallbackAction()
    {
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
