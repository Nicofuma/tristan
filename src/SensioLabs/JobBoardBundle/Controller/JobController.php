<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Event\JobBoardEvents;
use SensioLabs\JobBoardBundle\Event\JobsDisplayedEvent;
use SensioLabs\JobBoardBundle\Event\JobUpdatedEvent;
use SensioLabs\JobBoardBundle\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JobController extends Controller
{
    /**
     * @Route(
     *   name="job_show",
     *   path="/{country}/{contract}/{slug}",
     *   requirements={
     *      "country": "[A-Z]{2}",
     *      "contract": "[a-z-]+",
     *      "slug": "[0-9a-z-]+",
     *  }
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Template(template="@SensioLabsJobBoard/Job/show.html.twig")
     */
    public function showAction(Job $job)
    {
        $this->get('event_dispatcher')->dispatch(
            JobBoardEvents::JOB_DISPLAYED,
            new JobsDisplayedEvent([$job], JobRepository::VIEW_LOCATION_DETAILS)
        );

        return ['job' => $job];
    }

    /**
     * @Route(
     *   name="job_pay",
     *   path="/{country}/{contract}/{slug}/pay"
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Template
     */
    public function payAction(Job $ĵob)
    {
        return [];
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
     *   name="job_delete",
     *   path="/{country}/{contract}/{slug}/delete-{token}"
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Security("is_granted('JOB_DELETE', job)")
     */
    public function deleteAction(Job $job, $token)
    {
        if (!$this->isCsrfTokenValid('delete_job', $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($job);
        $em->flush();

        return $this->redirectToRoute('manage');
    }

    /**
     * @Route(
     *   name="job_update",
     *   path="/{country}/{contract}/{slug}/update"
     * )
     * @ParamConverter("job", options={"mapping"={"country"="country","contract"="contractType","slug"="slug"}})
     * @Security("is_granted('JOB_UPDATE', job)")
     * @Template()
     */
    public function updateAction(Request $request, Job $job)
    {
        $oldJob = clone $job;

        $form = $this->createForm('job', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('event_dispatcher')->dispatch(JobBoardEvents::JOB_UPDATE, new JobUpdatedEvent($oldJob, $job, JobUpdatedEvent::BY_USER));

            $em = $this->getDoctrine()->getManager();
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
}
