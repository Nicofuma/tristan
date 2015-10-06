<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Company;
use SensioLabs\JobBoardBundle\Entity\User;

abstract class AbstractFixture extends BaseAbstractFixture
{
    protected function getUser(ObjectManager $manager, $id)
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
            $manager->flush();
        }

        return $user;
    }

    protected function getCompany(ObjectManager $manager, $name = 'SensioLabs', $country = 'FR', $city = 'Paris')
    {
        $company = $manager->getRepository(Company::class)->findOneByFields($name, $country, $city);

        if (!$company) {
            $company = new Company();
            $company
                ->setName($name)
                ->setCountry($country)
                ->setCity($city)
            ;
            $manager->persist($company);
            $manager->flush();
        }

        return $company;
    }
}
