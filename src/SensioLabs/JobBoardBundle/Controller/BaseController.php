<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    const NB_JOBS_PER_PAGE = 10;

    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $repository = $this->getDoctrine()->getManager()->getRepository('SensioLabsJobBoardBundle:Job');
        $jobs = $repository->getAllFilteredWithBounds(
            $request->query->all(),
            ($page - 1) * self::NB_JOBS_PER_PAGE,
            self::NB_JOBS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('SensioLabsJobBoardBundle:Includes:job_container.html.twig', [
                'jobs' => $jobs,
            ]);
        }

        return [
            'locations' => $repository->countFilteredJobsPerCountry($request->query->all()),
            'contractTypes' => $repository->countFilteredJobsPerContractType($request->query->all()),
            'jobs' => $jobs,
        ];
    }

    /**
     * @Route("/post", name="job_post")
     * @Template()
     */
    public function postAction(Request $request)
    {
        $job = new Job();
        $form = $this->createForm('job', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($user = $this->getUser()) {
                $job->setUserName($user->getUsername());
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            return $this->redirectToRoute('job_preview', [
                'country' => $job->getCountry(),
                'contract' => $job->getContractType(),
                'slug' => $job->getSlug(),
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/manage", name="manage")
     * @Template()
     */
    public function manageAction()
    {
        return array();
    }
}
