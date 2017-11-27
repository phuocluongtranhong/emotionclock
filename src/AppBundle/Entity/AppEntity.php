<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/30/14
 * Time: 12:34
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AppEntity {
  /**
 * @ORM\Column(name="created_at", type="datetime", nullable=true)
 */
  protected $createdAt;

  /**
   * @ORM\ManyToOne(targetEntity="User")
   * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
   */
  protected $createdBy;

  /**
   * @ORM\Column(name="updated_at", type="datetime", nullable=true)
   */
  protected $updatedAt;

  /**
   * @ORM\ManyToOne(targetEntity="User")
   * @ORM\JoinColumn(name="updated_by", referencedColumnName="id", nullable=true)
   */
  protected $updatedBy;

  /**
   * @ORM\Column(name="delete_at", type="datetime", nullable=true)
   */
  protected $deleteAt;

  /**
   * @ORM\ManyToOne(targetEntity="User")
   * @ORM\JoinColumn(name="delete_by", referencedColumnName="id", nullable=true)
   */
  protected $deleteBy;

  /**
   * Get createdAt
   *
   * @return mixed
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Set createdAt
   *
   * @param mixed $createdAt
   * @return AppEntity
   */
  public function setCreatedAt($createdAt)
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  /**
   * Get createdBy
   *
   * @return mixed
   */
  public function getCreatedBy()
  {
    return $this->createdBy;
  }

  /**
   * Set createdBy
   *
   * @param mixed $createdBy
   * @return AppEntity
   */
  public function setCreatedBy($createdBy)
  {
    $this->createdBy = $createdBy;
    return $this;
  }

  /**
   * Get updatedAt
   *
   * @return mixed
   */
  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  /**
   * Set updatedAt
   *
   * @param mixed $updatedAt
   * @return AppEntity
   */
  public function setUpdatedAt($updatedAt)
  {
    $this->updatedAt = $updatedAt;
    return $this;
  }

  /**
   * Get updatedBy
   *
   * @return mixed
   */
  public function getUpdatedBy()
  {
    return $this->updatedBy;
  }

  /**
   * Set updatedBy
   *
   * @param mixed $updatedBy
   * @return AppEntity
   */
  public function setUpdatedBy($updatedBy)
  {
    $this->updatedBy = $updatedBy;
    return $this;
  }

  /**
   * Get deleteBy
   *
   * @return mixed
   */
  public function getDeleteBy()
  {
    return $this->deleteBy;
  }

  /**
   * Set deleteBy
   *
   * @param mixed $deleteBy
   * @return AppEntity
   */
  public function setDeleteBy($deleteBy)
  {
    $this->deleteBy = $deleteBy;
    return $this;
  }

  /**
   * Get deleteAt
   *
   * @return mixed
   */
  public function getDeleteAt()
  {
    return $this->deleteAt;
  }

  /**
   * Set deleteAt
   *
   * @param mixed $deleteAt
   * @return AppEntity
   */
  public function setDeleteAt($deleteAt)
  {
    $this->deleteAt = $deleteAt;
    return $this;
  }

  public function getClassName()
  {
    return get_class($this);
  }
} 