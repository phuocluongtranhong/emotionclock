<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/17/14
 * Time: 17:39
 */

namespace AppBundle\Services;

class EntityService {

  const BUNDLE = 'AppBundle';  // Bundle Name

  /**
   * @var EntityManager
   */
  private $em;

  /**
   * @var boolean
   */
  private $autoFlush = TRUE;

  /**
   * @param EntityManager $em
   */
  public function __construct($em)
  {
    $this->em = $em;
  }

  /**
   * @param EntityManager $em
   */
  public function setEntityManager($em)
  {
    $this->em = $em;
  }

  /**
   * @return EntityManager
   */
  public function getEntityManager()
  {
    return $this->em;
  }

  /**
   * @param boolean $isFlush
   */
  public function setAutoFlush($isFlush)
  {
    $this->autoFlush = $isFlush;
  }

  /**
   * @return boolean
   */
  public function getAutoFlush()
  {
    return $this->autoFlush;
  }

  /**
   * @param array $entities
   * @param boolean $isFlush
   */
  public function save($entities, $isFlush = 'DEFAULT')
  {
    if (!$entities)
      return;

    if (!is_array($entities)) {
      $entities = array($entities);
    }

    foreach ($entities as $entity) {
      $this->em->persist($entity);
    }

    if ($isFlush === 'DEFAULT')
      $isFlush = $this->getAutoFlush();

    if ($isFlush) {
      // echo "FLUSHED!". PHP_EOL;
      $this->em->flush();
    }
  }

  /**
   * @param String $table
   * @param Mixed $singleOrArray
   * @param boolean $isFlush
   */
  public function delete($table, $singleOrArray, $isFlush = 'DEFAULT')
  {
    if ($isFlush === 'DEFAULT')
      $isFlush = $this->getAutoFlush();

    return $this->process($table . ':delete', $singleOrArray, $isFlush);
  }

  /**
   *
   * @param $callback
   * @return mixed
   */
  public function process($callback)
  {
    list($entity, $method) = explode(':', $callback);
    $repositoryName = sprintf('AppBundle:%s', $entity);
    $repository = $this->em->getRepository($repositoryName);
    $handler = array($repository, $method);

    if (is_callable($handler)) {
      $params = func_get_args();
      array_shift($params);
      return call_user_func_array($handler, $params);
    }
  }

  /**
   *
   * @param $entities
   * @param $params
   * @param string $isFlush
   */
  public function updateExecute($entities, $params, $isFlush = 'DEFAULT')
  {
    if (!$entities) {
      return;
    }

    if (!is_array($entities)) {
      $entities = array($entities);
    }

    foreach ($entities as $entity) {
      foreach ($params as $key => $value) {
        if (method_exists($entity, 'set' . ucfirst($key))) {
          call_user_func(array($entity, 'set' . ucfirst($key)), $value);
        }
      }
    }

    $this->save($entities, $isFlush);
  }

  /**
   * Truncate a table
   *
   */
  public function truncate($tableName, $isFlush = 'DEFAULT' )
  {
    $cmd = $this->em->getClassMetadata('AppBundle:'. $tableName);   //テーブル名
    $connection = $this->em->getConnection();         //データベースのコンネクションを取得
    $dbPlatform = $connection->getDatabasePlatform();  //データベースプラトフォーム

    $connection->query('SET FOREIGN_KEY_CHECKS=0');   //外部キーのチェックを外す
    $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());  //TruncateのSQLを取得
    $connection->executeUpdate($q);    //コマンド実行
    $connection->query('SET FOREIGN_KEY_CHECKS=1');   //外部キーのチェックを戻す
  }
} 