<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
use SensioLabs\JobBoardBundle\Event\JobBoardEvents;
use SensioLabs\JobBoardBundle\Event\JobUpdatedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BackendController extends Controller
{
    /**
     * @Route("/backend", name="backend_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');
        $query = $repository->findAllQb();
        $repository->addStatusFilter($query, $request->query->get('status', JobStatus::PUBLISHED));

        $paginator = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            25,
            [
                'defaultSortFieldName' => 'j.createdAt',
                'defaultSortDirection' => 'asc',
                'wrap-queries' => true,
            ]
        );

        $paginator->setSortableTemplate('SensioLabsJobBoardBundle:Includes:backend_sortable_link.html.twig');
        $paginator->setTemplate('SensioLabsJobBoardBundle:Includes:backend_pagination.html.twig');

        return ['jobs' => $paginator];
    }

    /**
     * @Route("/backend/{id}/edit", name="backend_edit")
     * @Template()
     */
    public function editAction(Request $request, Job $job)
    {
        $oldJob = clone $job;

        $form = $this->createForm('job_admin', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('event_dispatcher')->dispatch(JobBoardEvents::JOB_UPDATE, new JobUpdatedEvent($oldJob, $job, JobUpdatedEvent::BY_ADMIN));

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $message = 'updated';
            if ($job->isValidated() && $job->isValidated() !== $oldJob->isValidated()) {
                $message = 'validated';
            }

            $this->addFlash('sucess', sprintf('Job %s %s.', $job->getTitle(), $message));

            return $this->redirectToRoute('backend_list');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *   name="backend_delete",
     *   path="/backend/delete",
     *   methods={"POST"}
     * )
     */
    public function deleteAction(Request $request)
    {
        $token = $request->request->get('token', '');
        if (!$this->isCsrfTokenValid('delete_job', $token)) {
            $this->addFlash('error', 'Invalid CSRF token.');
        } else {
            $em = $this->getDoctrine()->getManager();

            if (!$request->request->has('job_id')) {
                throw new BadRequestHttpException('The job id must be defined');
            }

            $jobId = $request->request->get('job_id');

            if (!is_numeric($jobId)) {
                throw new BadRequestHttpException('The job id must be an integer');
            }

            $job = $em->find(Job::class, $jobId);
            if (in_array($job->getStatus()->getValue(), [JobStatus::PUBLISHED, JobStatus::ARCHIVED], true)) {
                $this->addFlash('error', sprintf('You cannot delete %s, it must not be %s.', $job->getTitle(), $job->getStatus()->getReadable()));
            } else {
                $em->remove($job);
                $em->flush();

                $this->addFlash('sucess', sprintf('Job %s deleted.', $job->getTitle()));
            }
        }

        return $this->redirectToRoute('backend_list', json_decode($request->request->get('list_filters', '[]'), true));
    }
}
