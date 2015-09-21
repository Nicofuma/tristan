<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class JobController extends Controller
{
    /**
     * @Route("/show", name="job_show")
     * @Template()
     */
    public function showAction()
    {
        return array();
    }

    /**
     * @Route("/preview", name="job_preview")
     * @Template()
     */
    public function previewAction()
    {
        return array();
    }

    /**
     * @Route("/update", name="job_update")
     * @Template()
     */
    public function updateAction(Request $request)
    {
        return array();
    }
}
