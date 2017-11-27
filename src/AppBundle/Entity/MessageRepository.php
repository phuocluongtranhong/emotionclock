<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Libs\ValueUtil;
use AppBundle\Libs\ConfigUtil;

class MessageRepository extends AppRepository
{
    /**
     * @param AppBundle\Entity\User $user
     * @return array $arrMessageObjects
     * @author binh.vt
     */
    public function getNewMessageOfUser(\AppBundle\Entity\User $user) {
        $lastMessage = $user->getLastMessage();
        $query = $this->createQueryBuilder('m')
                    ->join('m.createdBy', 'u')
                    ->where('m.receiver = :user')
                    ->setParameter('user', $user);
        if ($lastMessage) {
            $query->andWhere('m.id > :lastMessage')
                    ->setParameter('lastMessage', $lastMessage);
        } else {
            //Do nothing.
        }
        return $query->getQuery()->getResult();
    }

    /**
     * This function statistic messsage per week.
     * @param AppBundle\Entity\User $user
     * @return array
     * @author binh.vt
     */
    public function getWeekStatisticByUser(\AppBundle\Entity\User $user) {
        //Day of Week format : 1 (for Monday) through 7 (for Sunday) ISO-8601.
        $daysOfWeek = [1,2,3,4,5,6,7];
        $emotions = ConfigUtil::getValueList('message.emotion');
        $dataResult = array(); //Format: $data[$day][$emotion] => count
        foreach ($daysOfWeek as $day) {
            foreach($emotions as $key => $name) {
                $dataResult[$day][$key] = 0;
            }
        }

        $monday = date('Y-m-d 00:00:00', strtotime('last Monday'));
        $today = date('Y-m-d 00:00:00', strtotime('tomorrow'));

        $query = $this->createQueryBuilder('m')
                        ->where('m.createdAt BETWEEN :fromDate AND :toDate')
                        ->setParameter('fromDate', $monday)
                        ->setParameter('toDate', $today)
                        ->andWhere('m.createdBy = :user')
                        ->setParameter('user', $user)
                        ->groupBy('m.createdAt');
        $query->orderBy('m.createdAt', 'DESC');
        $messages = $query->getQuery()->getResult();
        foreach($messages as $message) {
            $createdAt = $message->getCreatedAt();
            $emotion = $message->getEmotion();
            if ($createdAt && $emotion) {
                $dayOfWeek = date('N', date_timestamp_get($createdAt));

                if (isset($dataResult[$dayOfWeek][$emotion])) {
                    $dataResult[$dayOfWeek][$emotion] +=1;
                } else {
                    //Do nothing.
                }
            } else {
                //Do nothing.
            }
        }
        return $dataResult;
    }

    /**
     * @param AppBundle\Entity\User $user
     * @param int $limit
     * @param int offset
     * @return array
     * @author binh.vt + phuoc
     */
    public function getMessageHistory(\AppBundle\Entity\User $user, $limit = null, $offset= null) {
        //Get Message History of 1 month
        $fromDate = date('Y-m-01 00:00:00');
        $toDate = date('Y-m-t 23:59:59');
        $query = $this->createQueryBuilder('m')
                    ->where('m.createdBy = :user OR m.receiver = :user')
                    ->setParameter('user', $user)
                    ->andWhere('m.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', $fromDate)
                    ->setParameter('toDate', $toDate)
                    ->groupBy('m.createdAt')
//                    ->join('m.receiver', 'mr')
//                    ->join('m.createdBy', 'mc')
        ;

//        if (!empty($offset)) {
//            $query->andWhere('m.id < :offset')
//                ->setParameter('offset', $offset);
//        }
//        else {
//
//        }
        $query->orderBy('m.createdAt', 'DESC');
        $messages  = $query->getQuery()->setMaxResults($limit)->setFirstResult($offset)->getResult();
        $arrResults = array();
        foreach ($messages as $message) {
            $arrResults[] = $this->checkMessageType($user, $message);
        }
        return $arrResults;
    }

    /**
     * @param AppBundle\Entity\User $user
     * @return array
     * @author binh.vt
     */
    public function getYesterdayEmotion(\AppBundle\Entity\User $user) {
        //get EntityManager.
        $em = $this->getEntityManager();

        $arrResults = array();
        //Find the favorite emotion of yesterday.
        $yesterday = date('Y-m-d%', strtotime('yesterday'));
        $queryResult = $em->createQueryBuilder()
                        ->select('m.emotion, COUNT(m.id) as total')
                        ->from("AppBundle:Message", 'm')
                        ->where('m.createdAt LIKE :yesterday')
                        ->setParameter('yesterday', $yesterday)
                        ->andwhere('m.createdBy = :user')
                        ->setParameter('user', $user)
                        ->groupBy('m.emotion')
                        ->orderBy('total','DESC')
                        ->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if (isset($queryResult['emotion'])) {
            $arrResults['total'] = $queryResult['total'];
            $messages = $this->createQueryBuilder('m')
                            ->where('m.createdAt LIKE :yesterday')
                            ->setParameter('yesterday',  $yesterday)
                            ->andwhere('m.emotion = :emotion')
                            ->setParameter('emotion', $queryResult['emotion'])
                            ->andwhere('m.createdBy = :user')
                            ->setParameter('user', $user)
                            ->orderBy('m.createdAt', 'DESC')
                            ->getQuery()->getResult();
            foreach ($messages as $message) {
                $arrResults['messages'][] = $this->checkMessageType($user, $message);
            }
        }

        return $arrResults;
    }

    /**
     * @param User
     * @param Message
     * @return array
     * @author binh.vt
     */
    private function checkMessageType(\AppBundle\Entity\User $user, \AppBundle\Entity\Message $message) {
        $result = array(
            'message_id' => $message->getId(),
            'emotion' => $message->getEmotion(),
            'created_at' => date('Y-m-d H:i', date_timestamp_get($message->getCreatedAt())),
            'latitude' => $message->getLatitude(),
            'longitude' => $message->getLongitude(),
            'from' => null,
            'to' => null,
            'timestamp' => date_timestamp_get($message->getCreatedAt())
        );

        $sender = $message->getCreatedBy();
        $recevier = $message->getReceiver();

        if (!($sender && $recevier)) {
            continue;
        }

        $isSender = $user->getId() == $sender->getId();
        $isReceiver = $user->getId() == $recevier->getId();

        if ($isSender) {
            switch ($message->getType()) {
                case 0: //ValueUtil::constToValue('message.type.All_GROUP_NUMBERS'):
                    $result['to']['group'] = array(
                            'group_id' => $message->getFromGroup()->getId(),
                            'group_name' => $message->getFromGroup()->getName()
                        );

                    break;
                case 1://ValueUtil::constToValue('message.type.ONE_GROUP_NUMBER'):
                    $result['to']['user'] = array(
                            'user_id' => $recevier->getId(),
                            'user_name' => $recevier->getName(),
                            'group_id' => $message->getFromGroup()->getId(),
                            'group_name' => $message->getFromGroup()->getName()
                        );
                    break;
                default:
                    break;
            }
        }
        else if ($isReceiver) {
            $result['from']['user'] = array(
                'user_id' => $sender->getId(),
                'user_name' => $sender->getName(),
                'group_id' => $message->getFromGroup()->getId(),
                'group_name' => $message->getFromGroup()->getName()
            );
            switch ($message->getType()) {
                case 0: //ValueUtil::constToValue('message.type.All_GROUP_NUMBERS'):
                    $result['to']['group'] = array(
                        'group_id' => $message->getFromGroup()->getId(),
                        'group_name' => $message->getFromGroup()->getName()
                    );

                    break;
                default:
                    break;
            }
//            switch ($message->getType()) {
//                case ValueUtil::constToValue('message.type.All_GROUP_NUMBERS'):
//                    $result['from']['group'] = array(
//                            'group_id' => $message->getFromGroup()->getId(),
//                            'group_name' => $message->getFromGroup()->getName()
//                        );
//                    break;
//                case ValueUtil::constToValue('message.type.ONE_GROUP_NUMBER'):
//                    $result['from']['user'] = array(
//                            'user_id' => $sender->getId(),
//                            'user_name' => $sender->getName()
//                        );
//                    break;
//                default:
//                    break;
//            }
        }
        return $result;
    }
}
