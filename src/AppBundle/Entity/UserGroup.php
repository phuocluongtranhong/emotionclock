<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\Entity\UserGroup
 *
 * @ORM\Table(name="UserGroup")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserGroupRepository")
 */
class UserGroup extends AppEntity
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="default_receiver_id", referencedColumnName="id", nullable=true)
     */
    private $defaultReceiver;

    /**
     * @ORM\Column(name="role", type="boolean", nullable=true)
     * @Assert\NotBlank()
//     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="make_admin_by", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $makeAdminBy;

    /**
     * @ORM\Column(name="is_main", type="boolean", nullable=true)
     */
    private $isMain;

    /**
     * @ORM\Column(name="is_accept", type="boolean", nullable=true)
     */
    private $isAccept;
    /**
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(name="status", type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="admin_of_group", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $adminOfGroup;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param integer $role
     * @return UserGroup
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return integer
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set admin
     *
     * @param \AppBundle\Entity\User $user
     * @return UserGroup
     */
    public function setMakeAdminBy(\AppBundle\Entity\User $user = null)
    {
        $this->makeAdminBy = $user;

        return $this;
    }

    /**
     * Get makeAdmin
     *
     * @return \AppBundle\Entity\User
     */
    public function getMakeAdminBy()
    {
        return $this->makeAdminBy;
    }

    /**
     * Set isMain
     *
     * @param boolean $isMain
     * @return UserGroup
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * Get isMain
     *
     * @return boolean
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return UserGroup
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     * @return UserGroup
     */
    public function setGroup(\AppBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set defaultReceiver
     *
     * @param \AppBundle\Entity\User $defaultReceiver
     * @return UserGroup
     */
    public function setDefaultReceiver(\AppBundle\Entity\User $defaultReceiver = null)
    {
        $this->defaultReceiver = $defaultReceiver;

        return $this;
    }

    /**
     * Get defaultReceiver
     *
     * @return \AppBundle\Entity\User
     */
    public function getDefaultReceiver()
    {
        return $this->defaultReceiver;
    }



    /**
     * Set isAccept
     *
     * @param boolean $isAccept
     * @return UserGroup
     */
    public function setIsAccept($isAccept)
    {
        $this->isAccept = $isAccept;

        return $this;
    }

    /**
     * Get isAccept
     *
     * @return boolean
     */
    public function getIsAccept()
    {
        return $this->isAccept;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status = 0)
    {
        $this->status = $status;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return UserGroup
     */
    public function setAdminOfGroup(\AppBundle\Entity\User $adminOfGroup = null)
    {
        $this->adminOfGroup = $adminOfGroup;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getAdminOfGroup()
    {
        return $this->adminOfGroup;
    }

}
