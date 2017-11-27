<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/30/14
 * Time: 12:34
 */

namespace AppBundle\Entity;


use Bris\SharpBundle\Libs\ConfigUtil;
use Bris\SharpBundle\Libs\StringUtil;
use Bris\SharpBundle\Libs\ValueUtil;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppRepository extends EntityRepository implements ContainerAwareInterface{

  protected $container;
  private $alias;
  private $idx = 0;

  /**
   * @var QueryBuilder
   */
  private $query;

  /**
   * Sets the Container.
   *
   * @param ContainerInterface|null $container A ContainerInterface instance or null
   *
   * @api
   */
  public function setContainer(ContainerInterface $container = null){
    $this->container = $container;
  }

    /**
     * Build query with exclude deleted support
     * @param string $alias
     * @param Array $options
     *
     * @author Anhnt 2012/11/18
     */
    public function buildQuery($alias, $options = array()) {
        $query = $this->createQueryBuilder($alias);
        $this->alias = $alias;

        return $query;
    }

  /**
   * Build search query base on form search
   * @param string $alias
   * @param Form $form
   * @param Array $basic_fields
   *
   * @author Anhnt
   * @author modified Khiemnd 2012/12/04
   */
  protected function buildSearchQuery($alias, $form, $basic_fields = array()) {
    $this->query = $this->buildQuery($alias);

    if (isset($basic_fields['string'])) {
      foreach ($basic_fields['string'] as $field) {
        $this->addSearchString($form, $field);
      }
    }

      return $this->query;
    }


  /**
   * Delete single or multi row
   * @param mixed $idsOrEntities
   *
   * @author Khiemnd 2012/12/04
   */
  public function delete($idsOrEntities, $autoFlush = TRUE)
  {
    if (!is_array($idsOrEntities)) {
      $idsOrEntities = array($idsOrEntities);
    }

    $entities = array();
    $ids = array();
    $idToEntities = array();
    $notDeleteCount = count($idsOrEntities);

    foreach ($idsOrEntities as $idOrEntity) {
      if (is_numeric($idOrEntity)) {
        array_push($ids, $idOrEntity);
      }

      if (is_object($idOrEntity)) {
        array_push($entities, $idOrEntity);
      }
    }

    if (count($ids) > 0) {
      $idToEntities = $this->buildQuery('entity')
        ->andWhere('entity.id IN (:ids)')
        ->setParameter('ids', $ids)
        ->getQuery()
        ->getResult();
    }

    $em = $this->getEntityManager();

    foreach (array_merge($entities, $idToEntities) as $entity) {
      $em->remove($entity);
      $notDeleteCount--;
    }

    if ($notDeleteCount === 0) {
      if ($autoFlush) {
        $em->flush();
      }
    } else {
      throw new EntityException(ConfigUtil::getMessage('delete_fail'));
    }

  }


  /**
   * Build search query with value as string
   * @param Form $form
   * @param string $field
   *
   * @author Anhnt 2013/01/18
   */
  private function addSearchString($form, $field)
  {
    if ($form->has($field) && $form[$field]->getData()) {
      $fieldData = trim($form[$field]->getData());
      $fieldData = '%'. $fieldData . '%'; // both-hand match
      $dbFieldName = $this->dbFieldName($field);

      $this->query->andWhere(sprintf('%s LIKE :%s', $dbFieldName, $field))->setParameter($field, $fieldData);
    }
  }

  /**
   * Convert form field name to db field name
   * @param string $field
   * @return string $dbFieldName
   *
   * @author Anhnt 2013/01/11
   */
  private function dbFieldName($field)
  {
    if (StringUtil::contains($field, '_')) {
      $fieldName = str_replace('_', '.', $field); // ex: supplierStaff_kana => supplierStaff.kana
    } else {
      $fieldName = $this->alias . '.' . $field;
    }

    return $fieldName;
  }
}