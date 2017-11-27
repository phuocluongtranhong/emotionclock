<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CallScheduleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends AppRepository
{
    /**
     * get user info by email
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function loadUserByEmail($email)
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();

        return $user;
    }
}
