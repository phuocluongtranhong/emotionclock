<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Libs\ValueUtil;
use AppBundle\Libs\ConfigUtil;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;

use AppBundle\Entity\Message;
use AppBundle\Entity\Group;

/**
 * API controller (Rest API)
 * @Route("/api/v1/message")
 * @author phuoc
 */

class MessageController extends AppController
{
    /**
     * @Route("/sendMessage" )
     * @Method("POST")
     * @author binh.vt
     */
    public function sendMessageAction(Request $request) {
        //Get Request values.
        $emotion = $request->request->get('emotion');
        $groupId = $request->request->get('group_id');
        $longitude = $request->request->get('longitude');
        $latitude = $request->request->get('latitude');
        $isProduction = $request->request->get('production');
        $production = false;
        if (!empty($isProduction)) {
            $production = true;
        }

        $paramValidator = $this->validateParams([$emotion, $groupId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check group is existed
        $group = $em->getRepository('AppBundle:Group')->find($groupId);
        if (!$group) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        }

        //Get default recevier in group.
        $sender = $this->getUser();
        $userGroup = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
            'user' => $sender,
            'group' => $group
        ));
        if (!$userGroup) { //Check sender is a user of request group
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        } else {
            // Do nothing.
        }
        $defaultReceiver = $userGroup->getDefaultReceiver();

        //Create default values
        $arrReceivers = array();
        $typeOfMessage = null;

        if (empty($defaultReceiver)) { //If default recevier is null, System send message to all members of the group.
            $arrReceivers = $em->getRepository('AppBundle:UserGroup')->getUsersOfGroup($group);
            $typeOfMessage = ConfigUtil::getValue('message.type.All_GROUP_NUMBERS');
        } else { //Else System send only the default recevier
            $arrReceivers[] = $defaultReceiver;
            $typeOfMessage = ConfigUtil::getValue('message.type.ONE_GROUP_NUMBER');
        }

        $data = array();
        //Send message
        if ($arrReceivers) {
            $tokens = array();
            foreach($arrReceivers as $receiver) {
                if ($receiver->getId() != $sender->getId()) { //Don't send message to myself
                    $newMessages = new Message();
                    $newMessages->setReceiver($receiver);
                    $newMessages->setFromGroup($group);
                    $newMessages->setEmotion($emotion);
                    $newMessages->setType($typeOfMessage);

                    $newMessages->setLongitude($longitude);
                    $newMessages->setLatitude($latitude);

                    $em->persist($newMessages);

                    $token = $receiver->getUdId();
                    if ($token != null && !empty($token) && $receiver->getIsRunning()) {
                        $tokens[] = $token;
                    }
                }
            }
            $em->flush();

            $sender = $this->getUser()->getName();
            $icons = ['', 'Joy', 'OK', 'Meh', 'Sad', 'Fear', 'Angry'];
            $icon = $icons[$emotion];
            if ($typeOfMessage == ConfigUtil::getValue('message.type.All_GROUP_NUMBERS')) {
                $sms = $sender . " sent " . $icon . " to ". $group->getName(). " group.";
            } else {
                $sms = $sender . " sent " . $icon . " to you.";
            }



            if (!empty($tokens)) {
                $data = array(
                    "alert" => $sms,
//                    'type' => $icon
                    'type' => 'Emotion'
                );
                $this->sendIOSPN($data, $tokens, $production);
            }
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, $data);
    }

    /**
     * @Route("/sendInquiry" )
     * @Method("POST")
     * @author Phuoc
     */
    public function sendInquiryAction(Request $request) {
        //Get Request values.
        $groupId = $request->request->get('group_id');
        $isProduction = $request->request->get('production');
        $production = false;
        if (!empty($isProduction)) {
            $production = true;
        }

        $paramValidator = $this->validateParams([$groupId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check group is existed
        $group = $em->getRepository('AppBundle:Group')->find($groupId);
        if (!$group) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        }

        //Get default recevier in group.
        $sender = $this->getUser();
        $userGroup = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
            'user' => $sender,
            'group' => $group
        ));
        if (!$userGroup) { //Check sender is a user of request group
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        } else {
            // Do nothing.
        }
        $defaultReceiver = $userGroup->getDefaultReceiver();

        //Create default values
        $arrReceivers = array();
        $typeOfMessage = null;

        if (empty($defaultReceiver)) { //If default recevier is null, System send message to all members of the group.
            $arrReceivers = $em->getRepository('AppBundle:UserGroup')->getUsersOfGroup($group);
        } else { //Else System send only the default recevier
            $arrReceivers[] = $defaultReceiver;
        }

        $data = array();
        //Send message
        if ($arrReceivers) {
            $tokens = array();
            foreach($arrReceivers as $receiver) {
                if ($receiver->getId() != $sender->getId()) { //Don't send message to myself
                    $token = $receiver->getUdId();
                    if ($token != null && !empty($token)&& $receiver->getIsRunning()) {
                        $tokens[] = $token;
                    }
                }
            }

            $sender = $this->getUser()->getName();
            $sms = $sender . " asks how you are feeling.";


            if (!empty($tokens)) {
                $data = array(
                    "alert" => $sms,
                    'type' => 'Inquiry'
                );
                $this->sendIOSPN($data, $tokens, $production);
            }
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, $data);
    }

    /**
     * @Route("/getNewMessage", name="message_get_new_message")
     * @Method("POST")
     * @author binh.vt
     */
    public function getNewMessageAction(Request $request) {
        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Get New Messages
        $newMessages = $em->getRepository("AppBundle:Message")->getNewMessageOfUser($this->getUser());

        //Create Response
        $arrResults = array();
        foreach($newMessages as $newMessage) {
            $arrResults[] = array(
                'message_id' => $newMessage->getId(),
                'emotion' => $newMessage->getEmotion(),
                'created_by' => array(
                    'user_id' => $newMessage->getCreatedBy()->getId(),
                    'user_name' => $newMessage->getCreatedBy()->getName()
                ),
                'created_at' => date(TIME_FORMAT, date_timestamp_get($newMessage->getCreatedAt())),
                'longitude' => $newMessage->getLongitude(),
                'latitude' => $newMessage->getLatitude()
            );
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResults);
    }

    /**
     * @Route("/getWeekStatistic", name="message_get_week_statistic")
     * @Method("POST")
     * @author binh.vt
     */
     public function getWeekStatisticAction(Request $request) {
         //Day of Week format : 1 (for Monday) through 7 (for Sunday) ISO-8601.
         //Get EntityManager
         $em = $this->getDoctrine()->getManager();

         $weekStatistic = $em->getRepository("AppBundle:Message")->getWeekStatisticByUser($this->getUser());
         return $this->createJsonResponse(OK, SUCCESS_MSG, $weekStatistic);
     }

     /**
      * @Route("/getMessageHistory", name="get_message_history")
      * @Method("POST")
      * @author binh.vt
      */
     public function getMessageHistoryAction(Request $request) {
         $offsetId = $request->request->get('offset_id');
         $limit = $request->request->get('limit');
         if ($limit == null || $limit == 0) {
             $limit = 20;
         }
         $user = $this->getuser();

         $paramValidator = $this->validateParams([$offsetId]);
         if ($paramValidator != null) {
             return $paramValidator;
         }

        //GEt EntityManager
        $em = $this->getDoctrine()->getManager();

        //Get Messages
        $arrResults = $em->getRepository('AppBundle:Message')->getMessageHistory($user, $limit, $offsetId);
        return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResults);
     }

     /**
      * @Route("/getYesterdayEmotion")
      * @Method("POST")
      * @author binh.vt
      */
     public function getYesterdayEmotionAction(Request $request) {
         //GEt EntityManager
         $em = $this->getDoctrine()->getManager();

         //Get Messages
         $arrResults = $em->getRepository('AppBundle:Message')->getYesterdayEmotion($this->getUser());

         return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResults);
     }

     /**
      * @Route("/setLastMessage")
      * @Method("POST")
      * @author binh.vt
      */
     public function setLastMessage(Request $request) {
         $messageId = $request->request->get('message_id');

         $paramValidator = $this->validateParams([$messageId]);
         if ($paramValidator != null) {
             return $paramValidator;
         }

         //GEt EntityManager
         $em = $this->getDoctrine()->getManager();

         //Check messaage existed.
         $message = $em->getRepository("AppBundle:Message")->find($messageId);

         //Get user.
         $user = $this->getUser();

         if ($message) {
             if ($user->getId() == $message->getReceiver()->getId()) { //Seter and Receiver is not same a person.
                 $user->setLastMessage($message);
                 $em->flush();
             } else {
                 return $this->createJsonResponse(NG, ValueUtil::constToText("api_message.message.WRONG_INFO"),array());
             }
         } else {
             return $this->createJsonResponse(NG, ValueUtil::constToText("api_message.message.INVALID_MESSAGE"),array());;
         }
         return $this->createJsonResponse(OK, SUCCESS_MSG, array());
     }
}
