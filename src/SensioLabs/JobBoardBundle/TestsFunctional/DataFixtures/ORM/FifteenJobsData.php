<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Component\Intl\Intl;

class FifteenJobsData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $contractTypes = Job::getContractTypes();
        $countries = array_keys(Intl::getRegionBundle()->getCountryNames());

        for ($i = 0; $i < 15; ++$i) {
            $job = new Job();
            $job
                ->setTitle("#$i - FooBar Job")
                ->setDescription('This is the description of an amazing job!')
                ->setCompany('FooBar & Co')
                ->setContractType($contractTypes[$i % 5])
                ->setCity('Paris')
                ->setUser($this->getUser($manager, $i))
                ->setCountry($countries[$i % count($countries)])
                ->setHowToApply('Send an email to jobs@foobar.com')
                ->setCreatedAt(new \DateTime('+'.$i.' month'))
                ->setIsValidated()
                ->setPublishedAt(new \DateTime())
                ->setEndedAt(new \DateTime('+1 year'))
            ;

            $this->setReference('job-'.$i, $job);

            $manager->persist($job);
        }

        $manager->flush();
    }

    private function getUser(ObjectManager $manager, $id)
    {
        $user = $manager->getRepository(User::class)->findOneByUsername('user-'.$id);

        if (!$user) {
            $connectUser = new \SensioLabs\Connect\Api\Entity\User();
            $connectUser
                ->set('email', 'user-'.$id.'@example.org')
                ->set('username', 'user-'.$id)
                ->set('name', 'User '.$id)
                ->set('uuid', sprintf('%08d-1234-1234-1234-123456789012', $id));

            $user = new User($connectUser->get('uuid'));
            $user->updateFromConnect($connectUser);

            $this->setReference('user-'.$id, $user);

            $manager->persist($user);
        }

        return $user;
    }
}
