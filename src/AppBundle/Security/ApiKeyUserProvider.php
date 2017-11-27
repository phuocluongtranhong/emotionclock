<?php
// src/AppBundle/Security/ApiKeyUserProvider.php
namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use AppBundle\Entity\User;
use AppBundle\AppBundle;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     *@var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getUsernameForApiKey($apiKey)
    {
        $user = $this->em->getRepository("AppBundle:User")->findOneBy(array(
            'apiKey' => $apiKey
        ));

        if ($user) {
            //clear passwordTmp
            $user->setPasswordTmp(null);
            $this->em->flush();

            //Check api timelife
            $lastLogin = $user->getLastLogin();
            $lifeTime = time()-$lastLogin->getTimestamp();

            if ($lifeTime > 86400*30) { //Api is created over 1day, System will remove it.
//                throw new \Exception("The Api key expired", 401);
                return null;
            }

        } else {
            //Do nothing
        }

        return !empty($user) ? $user->getEmail() : null;
    }

    public function loadUserByUsername($username)
    {
        return $this->em->getRepository("AppBundle:User")->findOneBy(array(
            'email' => $username
        ));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \AppBundle\Entity\User ) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }
        // Refresh user
        $refreshUser = $this->em->getRepository('AppBundle:User')->find($user->getId());
        if($refreshUser){
            return $refreshUser;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $user->getUsername())
        );
    }

    public function supportsClass($class)
    {
        return 'AppBundle\Entity\User' === $class;
    }
}
