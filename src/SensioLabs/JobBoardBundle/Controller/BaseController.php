<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
use SensioLabs\JobBoardBundle\Event\JobBoardEvents;
use SensioLabs\JobBoardBundle\Event\JobsDisplayedEvent;
use SensioLabs\JobBoardBundle\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BaseController extends Controller
{
    const NB_JOB_PER_PAGE_ON_INDEX = 10;
    const NB_JOB_PER_PAGE_ON_MANAGE = 25;

    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');
        $query = $repository->findAllQb();
        $repository->addValidatedFilter($query);
        $repository->addDynamicFilters($query, $request->query->all());
        $repository->addStatusFilter($query, JobStatus::PUBLISHED);

        $jobs = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            self::NB_JOB_PER_PAGE_ON_INDEX
        );

        $this->get('event_dispatcher')->dispatch(
            JobBoardEvents::JOB_DISPLAYED,
            new JobsDisplayedEvent($jobs->getItems(), JobRepository::VIEW_LOCATION_HOMEPAGE)
        );

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
                $job->setUser($user);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            return $this->redirectToRoute('job_preview', [
                'country' => $job->getCompany()->getCountry(),
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
    public function manageAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');
        $query = $repository->findAllQb();
        $repository->addUserFilter($query, $this->getUser());

        $jobs = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            self::NB_JOB_PER_PAGE_ON_MANAGE
        );

        return ['jobs' => $jobs];
    }

    /**
     * @Route("/rss")
     */
    public function feedAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');

        $qb = $repository->findAllQb();
        $repository->addValidatedFilter($qb);
        $repository->addDynamicFilters($qb, $request->query->all());
        $repository->addStatusFilter($qb, JobStatus::PUBLISHED);
        $repository->addDateFilter($qb, new \DateTime());
        $repository->addOrderByPubishedDate($qb, 'DESC');

        $jobs = $qb->getQuery()->execute();

        $feed = $this->get('eko_feed.feed.manager')->get('jobs');
        $feed->addFromArray($jobs);

        return new Response($feed->render('rss'));
    }

    /**
     * @Route(
     *      pattern="/api/random",
     *      name="api_action"
     * )
     */
    public function apiRandomAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');

        $job = $repository->findOneRandom();

        return new JsonResponse([
            'title' => $job->getTitle(),
            'company' => $job->getCompany()->getName(),
            'city' => $job->getCompany()->getCity(),
            'country_name' => Intl::getRegionBundle()->getCountryName($job->getCompany()->getCountry()),
            'country_code' => $job->getCompany()->getCountry(),
            'contract' => $job->getContractType(),
            'url' => $this->generateUrl('job_show', [
                'contract' => $job->getContractType(),
                'country' => $job->getCompany()->getCountry(),
                'slug' => $job->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
