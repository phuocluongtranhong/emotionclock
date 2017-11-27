<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 12/12/14
 * Time: 14:03
 */

namespace AppBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;


class DoctrineExtensionListener
{
  private $container;

  public function __construct($container)
  {
    $this->container = $container;
  }

  /**
   * Doctrine pre-Persist event
   * @param LifecycleEventArgs $args
   */
  public function prePersist(LifecycleEventArgs $args)
  {
    //Tokenを取得
    $token = $this->container->get('security.context')->getToken();
    if ($token) {
      $entity = $args->getEntity();  //Entityを取得
      $user = $token->getUser();     //ログインユーザを取得

      //作成者・作成日時・更新者・更新日時の情報を設定
      $entity->setCreatedAt(new \DateTime());
      //$entity->setUpdatedAt(new \DateTime() );
      if ($user instanceof \AppBundle\Entity\User) {
        $entity->setCreatedBy($user);
        //$entity->setUpdatedBy($user);
      }
    }
  }

  /**
   * Doctrine pre-Update event
   * @param LifecycleEventArgs $args
   */
  public function preUpdate(LifecycleEventArgs $args)
  {
    //Tokenを取得
    $token = $this->container->get('security.context')->getToken();

    if ($token) {
      $entityManager = $args->getEntityManager();
      $unitOfWork = $entityManager->getUnitOfWork();

      $user = $token->getUser();    //ログインユーザを取得
      $entity = $args->getEntity(); //Entityを取得
      //if Screen lostPassword $user not a object
      if (!is_object($user)) {
        return false;
      }
      //更新者・更新日時の情報を設定
      $entity->setUpdatedAt(new \DateTime() );
      $entity->setUpdatedBy($user);

    }
  }
}
