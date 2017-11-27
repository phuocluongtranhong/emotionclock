<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * AppBundle\Entity\Group
 *
 * @ORM\Table(name="Groups")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\GroupRepository")
 */
class Group extends AppEntity
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", unique=true, nullable=false)
     * @Assert\NotBlank()
     */
    private $name;

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
     * @return Group
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
}
