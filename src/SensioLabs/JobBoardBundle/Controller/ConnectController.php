<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConnectController extends Controller
{
    /**
     * @Route("/login", name="login", options={"i18n":false})
     */
    public function loginAction(Request $request)
    {
        return $this->get('security.authentication.entry_point.sensiolabs_connect')->start($request);
    }

    /**
     * @Route("/sln_customizer.js", name="sln_customizer", options={"i18n":false})
     * @Template("SensioLabsJobBoardBundle:Connect:customizer.js.twig")
     */
    public function customizationAction()
    {
        return array();
    }

    /**
     * @Route("/session/callback", name="session_callback", options={"i18n":false})
     */
    public function sessionCallbackAction()
    {
    }

    /**
     * @Route("/logout", name="logout", options={"i18n":false})
     */
    public function logoutAction()
    {
    }
}
