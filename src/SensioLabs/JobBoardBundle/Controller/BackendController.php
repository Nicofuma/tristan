<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BackendController extends Controller
{
    /**
     * @Route("/backend", name="backend_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/backend/{id}/edit", name="backend_edit")
     * @Template()
     */
    public function editAction(Request $request, Job $job)
    {
        return array();
    }
}
