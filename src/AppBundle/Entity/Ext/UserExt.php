<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 4/21/15
 * Time: 11:34
 */

namespace AppBundle\Entity\Ext;

use AppBundle\Entity\User;
use AppBundle\Libs\ValueUtil;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserExt {

    private $confirmPassword;

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return NULL;
    }


    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        $roles = array();
        $roles[] = 'ROLE_USER';
        return $roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(){
        return $this->email;
    }

    /**
     * Get confirmPassword
     *
     * @return mixed
     */
    public function getConfirmPassword()
    {
        return $this->confirmPassword;
    }

    /**
     * Set confirmPassword
     *
     * @param mixed $confirmPassword
     * @return UserExt
     */
    public function setConfirmPassword($confirmPassword)
    {
        $this->confirmPassword = $confirmPassword;
        return $this;
    }

}
