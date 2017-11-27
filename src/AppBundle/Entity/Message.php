<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\Entity\Message
 *
 * @ORM\Table(name="Message")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\MessageRepository")
 */
class Message extends AppEntity
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id", nullable=false)
     */
    private $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="from_group", referencedColumnName="id", nullable=false)
     */
    private $fromGroup;

    /**
     * @ORM\Column(name="emotion", type="smallint", nullable=false)
     */
    private $emotion;

    /**
     * @ORM\Column(name="type", type="boolean", length= 1, nullable=false)
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @ORM\Column(name="longitude", type="string", length=25, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(name="latitude", type="string", length=25, nullable=true)
     */
    private $latitude;


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
     * Set emotion
     *
     * @param integer $emotion
     * @return Message
     */
    public function setEmotion($emotion)
    {
        $this->emotion = $emotion;

        return $this;
    }

    /**
     * Get emotion
     *
     * @return integer
     */
    public function getEmotion()
    {
        return $this->emotion;
    }

    /**
     * Set type
     *
     * @param boolean $type
     * @return Message
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return boolean
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return Message
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return Message
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set receiver
     *
     * @param \AppBundle\Entity\User $receiver
     * @return Message
     */
    public function setReceiver(\AppBundle\Entity\User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \AppBundle\Entity\User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set fromGroup
     *
     * @param \AppBundle\Entity\Group $fromGroup
     * @return Message
     */
    public function setFromGroup(\AppBundle\Entity\Group $fromGroup)
    {
        $this->fromGroup = $fromGroup;

        return $this;
    }

    /**
     * Get fromGroup
     *
     * @return \AppBundle\Entity\Group 
     */
    public function getFromGroup()
    {
        return $this->fromGroup;
    }
}
