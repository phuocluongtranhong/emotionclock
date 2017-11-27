<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * AppBundle\Entity\User
 *
 * @ORM\Table(name="User")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User extends AppEntity implements UserInterface
{
    use Ext\UserExt;
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(name="password", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @ORM\Column(name="password_tmp", type="string", nullable=true)
     */
    private $passwordTmp;

    /**
     * @ORM\Column(name="email", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(name="phone_number", type="string", length=12, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(name="ud_id", type="string", nullable=true)
     */
    private $udId;

    /**
     * @ORM\Column(name="api_key", type="string", unique=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\ManyToOne(targetEntity="Message")
     * @ORM\JoinColumn(name="last_message_id", referencedColumnName="id", nullable=true)
     */
    private $lastMessage;


    /**
     * @ORM\Column(name="is_running", type="integer")
     * @Assert\NotBlank()
     */
    private $isRunning;


    /**
     * @ORM\Column(name="notification_count", type="integer")
     * @Assert\NotBlank()
     */
    private $notificationCount;


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
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set passwordTmp
     *
     * @param string $passwordTmp
     * @return User
     */
    public function setPasswordTmp($passwordTmp)
    {
        $this->passwordTmp = $passwordTmp;

        return $this;
    }

    /**
     * Get passwordTmp
     *
     * @return string
     */
    public function getPasswordTmp()
    {
        return $this->passwordTmp;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set udId
     *
     * @param string $udId
     * @return User
     */
    public function setUdId($udId)
    {
        $this->udId = $udId;

        return $this;
    }

    /**
     * Get udId
     *
     * @return string
     */
    public function getUdId()
    {
        return $this->udId;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     * @return User
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set lastMessage
     *
     * @param \AppBundle\Entity\Message $lastMessage
     * @return User
     */
    public function setLastMessage(\AppBundle\Entity\Message $lastMessage = null)
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * Get lastMessage
     *
     * @return \AppBundle\Entity\Message 
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    /**
     * Set isRunning
     *
     * @param boolean $isRunning
     * @return User
     */
    public function setIsRunning($isRunning)
    {
        $this->isRunning = $isRunning;
        return $this;
    }

    /**
     * Get isRunning
     *
     * @return boolean
     */
    public function getIsRunning()
    {
        return $this->isRunning;
    }

    /**
     * Set notificationCount
     *
     * @param integer $notificationCount
     * @return User
     */
    public function setNotificationCount($notificationCount)
    {
        $this->notificationCount = $notificationCount;
        return $this;
    }

    /**
     * Get notificationCount
     *
     * @return integer
     */
    public function getNotificationCount()
    {
        return $this->notificationCount;
    }

}
