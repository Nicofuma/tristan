<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SensioLabs\JobBoardBundle\Entity\Job;
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
     * @Route(
     *   name="job_preview",
     *   path="/{country}/{contract}/{slug}/preview"
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Template()
     */
    public function previewAction(Job $job)
    {
        return ['job' => $job];
    }

    /**
     * @Route(
     *   name="job_update",
     *   path="/{country}/{contract}/{slug}/update"
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Template()
     */
    public function updateAction(Request $request, Job $job)
    {
        return array();
    }
}
