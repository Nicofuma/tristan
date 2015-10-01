<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SensioLabs\JobBoardBundle\Entity\Job;
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
        $query = $repository->getAllPublishedQueryBuilder();

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
        return array();
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
            if ($job->isPublished()) {
                $this->addFlash('error', sprintf('You cannot delete %s, it must not be published.', $job->getTitle()));
            } else {
                $em->remove($job);
                $em->flush();

                $this->addFlash('sucess', sprintf('Job %s deleted.', $job->getTitle()));
            }
        }

        return $this->redirectToRoute('backend_list', json_decode($request->request->get('list_filters', '[]'), true));
    }
}
