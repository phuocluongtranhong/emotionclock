<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Libs\ValueUtil;
use AppBundle\Libs\ConfigUtil;

use AppBundle\Entity\Group;
use AppBundle\Entity\UserGroup;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Null;


/**
 * API controller (Rest API)
 *
 * @Route("/api/v1/group")
 * @author Phuoc
 */



class GroupController extends AppController
{
    //Request
    //AcceptedInvitation
    //DeclinedInvitation
    
    /**
     * @Route("/getGroupsInfo" )
     * @Method("POST")
     * @author binh.vt
     */
    public function getGroupsByUserIdAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        //Get UserGroups
        $arrUserGroup = null;
        if ($this->getUser()) { //Check user is not empty
            $arrUserGroup = $em->getRepository('AppBundle:UserGroup')->getUserGroupsByUser($this->getUser());
        } else {
            throw new \Exception('The User is empty', 401);
        }

        $arrResult = array();
        foreach ($arrUserGroup as $usergroup) {
            $defaultReceiver = $usergroup->getDefaultReceiver();
            $defaultReceiverId = null;
            $defaultReceiverName = null;
            if ($defaultReceiver) {
                $defaultReceiverId = $defaultReceiver->getId();
                $defaultReceiverName = $defaultReceiver->getName();
            }
            $arrResult[] = array(
                'usergroup_id' => $usergroup->getId(),
                'default_receiver_id' => $defaultReceiverId,
                'default_receiver_name' => $defaultReceiverName,
                'user_id' => $usergroup->getUser()->getId(),
                'user_name' => $usergroup->getUser()->getName(),
                'role' => $usergroup->getRole(),
                'is_main' => $usergroup->getIsMain(),
                'is_accept' => $usergroup->getIsAccept(),
                'group_id' => $usergroup->getGroup()->getId(),
                'group_name' => $usergroup->getGroup()->getName(),

            );
        }

        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrResult);
    }

    /**
     * @Route("/getAllUserInGroup")
     * @Method("POST")
     * @author phuoc
     */
    public function getAllUserInGroup(Request $request) {
        $groupId = $request->request->get('group_id');

        $paramValidator = $this->validateParams([$groupId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        $em = $this->getDoctrine()->getManager();
        //check exist group
        $group = $em->getRepository('AppBundle:Group')->findOneById($groupId);

        if (empty($group)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        }

        $arrUser = null;
        if ($this->getUser()) {
            $arrUser = $em->getRepository('AppBundle:UserGroup')->getUsersOfGroup($group);
        } else {
            throw new \Exception('The User is empty', 401);
        }

        $arrResult = array();
        foreach ($arrUser as $user) {
            $arrResult[] = array(
                'user_id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone_number' => $user->getPhoneNumber(),
            );
        }
        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrResult);
    }

    /**
     * @Route("/addGroup")
     * @Method("POST")
     * @author binh.vt
     */
    public function addGroupAction(Request $request) {
        $group_name = $request->request->get('group_name');
        $paramValidator = $this->validateParams([$group_name]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        //Check group name is not exsited.
        $isExisted = $em->getRepository('AppBundle:Group')->findOneBy(array(
            'name' => $group_name
        ));
        if ($isExisted) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.GROUP_NAME_AVAILABLE'), array());
        } else {
            //Do nothing.
        }

        //Create a new group.
        $group = new Group();
        $group->setName($group_name);
        $em->persist($group);
        $em->flush();

        //Create relation between user and new group.
        try {
            $userGroup = new UserGroup();
            $userGroup->setGroup($group);
            $userGroup->setUser($this->getUser());
            $userGroup->setRole(ValueUtil::constToValue('group.role.ADMIN'));
            $userGroup->setIsAccept(ValueUtil::constToValue('group.accept_status.APPCEPT_STATUS'));

            $mainGroup = $em->getRepository('AppBundle:UserGroup')->getMainGroupByUser($this->getUser());
            if (empty($mainGroup)) {
                $userGroup->setIsMain(1);
            }

            $em->persist($userGroup);
            $em->flush();
        } catch(Exception $e) {
            $em->remove($group);
            $em->flush();
            throw $e;
        }

        $arrResult = array(
            'group_id' => $group->getId(),
            'group_name' => $group->getName()
        );

        return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResult);
    }

    /**
     * @Route("/updateGroupInfo" )
     * @Method("POST")
     * @author binh.vt
     */
    public function updateGroupInfo(Request $request) {
        $groupId = $request->get('group_id');
        $newGroupName = $request->request->get("group_name");
        $paramValidator = $this->validateParams([$groupId, $newGroupName]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        $arrayResult = array();

        //Get current User.
        $user = $this->getUser();
        $isGroupMember = $em->getRepository('AppBundle:UserGroup')->isGroupMember($groupId, $user);
        if ($isGroupMember) { //Check user is a member of the group
            if ($isGroupMember->getRole()) { //Check user is admin of the group
                $group = $isGroupMember->getGroup();
                $group->setName($newGroupName);
                $em->flush();
                $arrayResult = array(
                    'group_name' => $group->getName(),
                    'group_id' => $group->getId()
                );
            } else {
                return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
            }
        } else {
            return $this->createJsonResponse(NG, ConfigUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        }
        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrayResult);
    }

    /**
     * @Route("/addMember" )
     * @Method("POST")
     * @author phuoc
     */
    public function addMemberAction(Request $request) {
        $groupId = $request->get('group_id');
        $memberEmail = $request->get("member_email");
        $paramValidator = $this->validateParams([$groupId, $memberEmail]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check Group existed
        $group = $em->getRepository('AppBundle:Group')->find($groupId);
        if (empty($group)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        }

        //Check Email.
        $memberUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'email' => $memberEmail
        ));
        if ($memberUser == null) {
            $link = 'http://www.w3schools.com/html/html_links.asp';
            $title = '[Clock-emotional] You are invited to join '. $group->getName() . ' group';
            $body = $this->renderView('AppBundle:Group:invite_email.html.twig', array(
                'sender' => $this->getUser()->getEmail(),
                'groupName' => $group->getName(),
                'link' => $link
            ), 'text/html; charset=UTF-8');

            //get mailer.
            $mailer = $this->get('mailer');
            $isSuccessfull = ConfigUtil::sendMail($mailer, $memberEmail, $title, $body);
            if (!$isSuccessfull) {
                return $this->createJsonResponse(ERROR_SERVER, ValueUtil::constToText('api_message.common.EMAIL_NOT_SEND'), array());
            }

            $userGroup = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
                'email' => $memberEmail,
                'group' => $group,
                'createdBy' => $this->getUser()
            ));
            if (!$userGroup) { //Check sender is a user of request group
                $userGroup = new UserGroup();
                $userGroup->setEmail($memberEmail);
                $userGroup->setGroup($group);
                $em->persist($userGroup);
                $em->flush();
            }

            return $this->createJsonResponse(OK, SUCCESS_MSG, array('alert' => 'Send inviation to your email'));
        } else {
            //Check User is not a member of the group.
            $isMember = $em->getRepository('AppBundle:UserGroup')->isGroupMember($group, $memberUser);
            //isMember and not deleted
            if ($isMember && ($isMember->getDeleteBy() == null)) { //If User is a member, System warn user of that.
                return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_MEMBER'), array());
            }
        }

        //create Group and User relationship.
        $userGroup = new UserGroup();
        $userGroup->setGroup($group);
        $userGroup->setUser($memberUser);
        $userGroup->setRole(ValueUtil::constToValue('group.role.GROUP_MEMBER'));
        $em->persist($userGroup);
        $em->flush();

        if ($memberUser->getUdId() && $memberUser->getIsRunning()) {
            $sms = $this->getUser()->getName() . " invited you to join " . $group->getName() . " group.";
            $data = array(
                "alert" => $sms,
                'type' => "Request",
            );
            $tokens = array();
            $tokens[] = $memberUser->getUdId();
            $this->sendIOSPN($data, $tokens, true);
            $this->increaseNotificationCount($memberUser->getId());
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/replyInvitation")
     * Method("POST")
     */
    public function replyInvitation(Request $request)
    {
        $usergroupId = $request->request->get('usergroup_id');
        $isAccept = $request->request->get('is_accept');
        $paramValidator = $this->validateParams([$usergroupId, $isAccept]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        if ($this->getUser()) { //Check user is not empty

        } else {
            throw new \Exception('The User is empty', 401);
        }

        //get EntityManager.
        $em = $this->getDoctrine()->getManager();

        //get Invitaion.
        $userGroup = $em->getRepository('AppBundle:UserGroup')->find($usergroupId);

        if ($userGroup) { //If invitation is created and flag is null, we check status from the device.
            $sms = '';
            $type = '';

            $tokens = array();
            $token = $userGroup->getCreatedBy()->getUdId();
            $tokens[] = $token;
            $createdBy = $this->getUser()->getName();
            switch ($isAccept) {
                case 1: #System active user to become a member of the group.
                    $userGroup->setIsAccept($isAccept);
                    $userGroup->setStatus(1); //not confirm
                    $userGroup->setUser($this->getUser());
                    $userGroup->setEmail(null);
                    $sms = $createdBy . ' accepted your invitation.';
                    $type = "AcceptedInvitation";
                    break;

                default: #System will delete record, because User refused the Invitaion.
                    $userGroup->setIsAccept(0);
                    $userGroup->setStatus(1); //not confirm
                    $userGroup->setUser($this->getUser());
                    $userGroup->setEmail(null);
                    $sms = $createdBy . ' declined your invitation.';
                    $type = "DeclinedInvitation";
                    break;
            }
            $em->flush();

            if (!empty($tokens) && $userGroup->getCreatedBy()->getIsRunning()) {
                $data = array(
                    "alert" => $sms,
                    'type' => $type,
                );
                $this->sendIOSPN($data, $tokens, true);
                $this->increaseNotificationCount($userGroup->getCreatedBy()->getId());
            }

        } else {//The invitation is not created.
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.INVITATION_NOT_EXISTED'));
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/confirmInvitation")
     * Method("POST")
     */

    public function confirmInvitation(Request $request)
    {
        $usergroupId = $request->request->get('usergroup_id');

        $paramValidator = $this->validateParams([$usergroupId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        if ($this->getUser()) { //Check user is not empty

        } else {
            throw new \Exception('The User is empty', 401);
        }

        //get EntityManager.
        $em = $this->getDoctrine()->getManager();

        //get Invitaion.
        $userGroup = $em->getRepository('AppBundle:UserGroup')->find($usergroupId);

        if ($userGroup) { //If invitation is created and flag is null, we check status from the device.
            $isAccept = $userGroup->getIsAccept();
            switch ($isAccept) {
                case 1: #System active user to become a member of the group.
                    $userGroup->setStatus(0);
                    break;
                default: #System will delete record, because User refused the Invitaion.
                    $em->remove($userGroup);
                    break;
            }
            $em->flush();
        }
        else {//The invitation is not created.
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.INVITATION_NOT_EXISTED'));
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/getInvitations")
     * Method("POST")
     */
    public function getInvitations(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $arrUserGroup = null;
        if ($this->getUser()) { //Check user is not empty
            $arrUserGroup = $em->getRepository('AppBundle:UserGroup')->getInvitations($this->getUser());
        } else {
            throw new \Exception('The User is empty', 401);
        }

        $arrResult = array();
        foreach ($arrUserGroup as $usergroup) {

            if ($usergroup->getUser()) {
                $userId = $usergroup->getUser()->getId();
                $userName = $usergroup->getUser()->getName();
            }
            else {
                $userId = -1;
                $userName = '';
            }

            $arrResult[] = array(
                'usergroup_id' => $usergroup->getId(),
                'created_by_id' => $usergroup->getCreatedBy()->getId(),
                'created_by_name' => $usergroup->getCreatedBy()->getName(),

                'user_id' => $userId,
                'user_name' => $userName,

                'is_accept' => $usergroup->getIsAccept(),
                'group_id' => $usergroup->getGroup()->getId(),
                'group_name' => $usergroup->getGroup()->getName(),
            );
        }

        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrResult);
    }


    /**
     * @Route("/getSendInvitations")
     * Method("POST")
     */
    public function getSendInvitations(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $arrUserGroup = null;
        if ($this->getUser()) { //Check user is not empty
            $arrUserGroup = $em->getRepository('AppBundle:UserGroup')->getSendInvitations($this->getUser());
        } else {
            throw new \Exception('The User is empty', 401);
        }

        $arrResult = array();
        foreach ($arrUserGroup as $usergroup) {
            $userName = '';
            $userId = 0;
            if ($usergroup->getUser() == null) {
                $userName = $usergroup->getEmail();
            }
            else {
                $userName = $usergroup->getUser()->getName();
                $userId = $usergroup->getUser()->getId();
            }

            $arrResult[] = array(
                'usergroup_id' => $usergroup->getId(),
                'created_by_id' => $usergroup->getCreatedBy()->getId(),
                'created_by_name' => $usergroup->getCreatedBy()->getName(),
                'user_id' => $userId,
                'user_name' => $userName,

                'is_accept' => $usergroup->getIsAccept(),
                'group_id' => $usergroup->getGroup()->getId(),
                'group_name' => $usergroup->getGroup()->getName(),
                'status' => $usergroup->getStatus(),
            );
        }

        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrResult);
    }


    /**
     * @Route("/setDefaultGroup")
     * @Method("POST")
     * @author binh.vt  
     */
    public function setDefaultGroupAction(Request $request) {
        $groupId = $request->request->get('group_id');
        $paramValidator = $this->validateParams([$groupId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //get EntityManager
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $isMember = $em->getRepository('AppBundle:UserGroup')->isGroupMember($groupId, $user);
        $count = 0;
        if ($isMember) {
            $count = $em->getRepository('AppBundle:UserGroup')->setMainGroup($groupId, $user);
        } else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array(
            'affected_rows' => $count
        ));
    }

    /**
     * @Route("/setDefaultReceiver")
     * @Method("POST")
     * @author binh.vt
     */
     public function setDefaultReceiverAction(Request $request) {
         $receiverId = $request->request->get('receiver_id'); #all|number
         $groupId = $request->request->get('group_id');
         $paramValidator = $this->validateParams([$groupId, $receiverId]);
         if ($paramValidator != null) {
             return $paramValidator;
         }

         //get EntityManager
         $em = $this->getDoctrine()->getManager();

         //Set Value for Recevier.
         $receiver = null;
         if ($receiverId != 'all') {
             //Check receiver is a member of the group.
             $receiverGroup = $em->getRepository('AppBundle:UserGroup')->isGroupMember($groupId, $receiverId);
             if (!$receiverGroup) {
                 return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'));
             } else {
                 //Do nothing.
             }
             $receiver = $receiverGroup->getUser();
         } else {
             //Do nothing.
         }

         //Check caller is a member of the group.
         $userGroup = $em->getRepository('AppBundle:UserGroup')->isGroupMember($groupId, $this->getUser());
         if (!$userGroup) {
             return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'));
         } else {
             $userGroup->setDefaultReceiver($receiver);
             $em->flush();
         }

         return $this->createJsonResponse(OK, SUCCESS_MSG, array());
     }


    /**
     * @Route("/leaveGroup")
     * @Method("POST")
     * @author Phuoc
     */
    public function leaveGroupAction(Request $request) {
        $usergroup_id = $request->request->get('usergroup_id');
        $paramValidator = $this->validateParams([$usergroup_id]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        $usergroup = $em->getRepository('AppBundle:UserGroup')->find($usergroup_id);

        if (empty($usergroup)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.INVALID'), array());
        }

        $member = $usergroup->getUser();

        if (!$usergroup->getRole()) { //is member
            if ($this->getUser()->getId() != $member->getId()) {
                return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
            }
            else {

                //find admin of group
                $tmpUserGroup = $em->getRepository('AppBundle:UserGroup')->findAdmin($usergroup->getGroup());
                //exist admin
                if (!empty($tmpUserGroup)) {
                    $tokens = array();

                    $token = $tmpUserGroup->getUser()->getUdId();
                    $tokens[] = $token;

                    if (!empty($tokens) && $tmpUserGroup->getUser()->getIsRunning()) {
                        $sms = $this->getUser()->getName() . " left " . $usergroup->getGroup()->getName() . " group.";
                        $data = array(
                            "alert" => $sms,
                            'type' => "LeaveGroup",
                        );
                        $this->sendIOSPN($data, $tokens, true);
                        $this->increaseNotificationCount($tmpUserGroup->getUser()->getId());
                    }
                    $usergroup->setAdminOfGroup($tmpUserGroup->getUser());
                }

                $usergroup->setDeleteBy($this->getUser());

                $em->flush();

                $arrResult = array(
                    'usergroup_id' => $usergroup->getId(),
                    'created_by_id' => $usergroup->getCreatedBy()->getId(),
                    'created_by_name' => $usergroup->getCreatedBy()->getName(),

                    'user_id' => $usergroup->getUser()->getId(),
                    'user_name' => $usergroup->getUser()->getName(),

                    'group_id' => $usergroup->getGroup()->getId(),
                    'group_name' => $usergroup->getGroup()->getName(),
                );
                return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResult);
            }
        }
        else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }

    /**
     * @Route("/confirmLeaveGroup")
     * @Method("POST")
     * @author Phuoc
     */
    public function confirmLeaveGroupAction(Request $request) {
        $usergroup_id = $request->request->get('usergroup_id');
        $paramValidator = $this->validateParams([$usergroup_id]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        $userGroup = $em->getRepository('AppBundle:UserGroup')->find($usergroup_id);
        if (empty($userGroup)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.INVALID'));
        }

        //check admin
        if ( !$userGroup->getRole() && $userGroup->getUser()->getId() != $userGroup->getDeleteBy()->getId() ) { //member
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
        }

        $deleteBy = $userGroup->getDeleteBy();

        //check member leave group
        if ( ($deleteBy->getId() != $this->getUser()->getId()) ||
            ($deleteBy->getId() == $this->getUser()->getId() && $deleteBy != $this->getAdminOfGroup()) ) {
            $em->remove($userGroup);
            $em->flush();
        }
        else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/removeMember")
     * @Method("POST")
     * @author binh.vt
     */
    public function removeMemberAction(Request $request) {
        $groupId = $request->request->get('group_id');
        $memberId = $request->request->get('member_id');

        $paramValidator = $this->validateParams([$groupId, $memberId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check Group existed
        $group = $em->getRepository('AppBundle:Group')->find($groupId);
        if (empty($group)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        } else {
            //Do nothing.
        }

        //Check User is a member of the group.
        $user = $this->getUser();
        $isGroupMember = $em->getRepository('AppBundle:UserGroup')->isGroupMember($group, $user);

        if (!empty($isGroupMember)) { //If User is a member, System warn user of that.
            if ($isGroupMember->getRole()) {
                //Check user is admin of the group
                $usergroup = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
                    'group' => $group,
                    'user' => $memberId
                ));
                if (!empty($usergroup)) { //update deleteBy is user
                    if ($user->getId() != $memberId) //admin is not member
                    {
                        $usergroup->setDeleteBy($user);
                        $em->flush();

                        $member = $usergroup->getUser();
                        $tokens = array();
                        $token = $member->getUdId();
                        $tokens[] = $token;

                        if (!empty($tokens) && $member->getIsRunning()) {
                            $sms = $user->getName() . " removed you from " . $usergroup->getGroup()->getName() . " group.";
                            $data = array(
                                "alert" => $sms,
                                'type' => "RemoveMember",
                            );
                            $this->sendIOSPN($data, $tokens, true);
                            $this->increaseNotificationCount($member->getId());
                        }

                        $arrResult = array(
                            'usergroup_id' => $usergroup->getId(),
                            'created_by_id' => $usergroup->getCreatedBy()->getId(),
                            'created_by_name' => $usergroup->getCreatedBy()->getName(),

                            'user_id' => $member->getId(),
                            'user_name' => $member->getName(),

                            'group_id' => $usergroup->getGroup()->getId(),
                            'group_name' => $usergroup->getGroup()->getName(),
                        );
                        return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResult);
                    }
                }
            }
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());

        } else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }

    /**
     * @Route("/confirmRemoveMember")
     * @Method("POST")
     * @author phuoc
     */
    public function confirmRemoveMemberAction(Request $request) {
        $usergroup_id = $request->request->get('usergroup_id');
        $paramValidator = $this->validateParams([$usergroup_id]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        $userGroup = $em->getRepository('AppBundle:UserGroup')->find($usergroup_id);
        if (empty($userGroup)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.INVALID'));
        }

        $user = $this->getUser();//member
        $deleteBy = $userGroup->getDeleteBy();
        $member = $userGroup->getUser();//member

        //Member confirm
        if ($user->getId() == $member->getId() && $user->getId() != $deleteBy->getId()) {
            $em->remove($userGroup);
            $em->flush();
        }
        else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
        }
        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/getOtherNotifications")
     * @Method("POST")
     * @author Phuoc
     */
    public function getOtherNotificationsAction(Request $request)
    {
        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check User is a member of the group.
        $user = $this->getUser();
        $userGroups = $em->getRepository('AppBundle:UserGroup')->getOtherNotifications($user);

        if (!empty($userGroups)) {
            $arrResult = array();
            foreach ($userGroups as $usergroup) {
                $deleteBy = $usergroup->getDeleteBy();
                $deleteById = null;
                $deleteByName = null;
                if ($deleteBy != null) {
                    $deleteById = $deleteBy->getId();
                    $deleteByName = $deleteBy->getName();
                }

                $makeAdminBy = $usergroup->getMakeAdminBy();
                $makeAdminByName = null;
                if ($makeAdminBy != null) {
                    $makeAdminByName = $makeAdminBy->getName();
                }

                $arrResult[] = array(
                    'usergroup_id' => $usergroup->getId(),
                    'created_by_id' => $deleteById,
                    'created_by_name' => $deleteByName,

                    'user_id' => $usergroup->getUser()->getId(),
                    'user_name' => $usergroup->getUser()->getName(),

                    'is_accept' => $usergroup->getIsAccept(),
                    'group_id' => $usergroup->getGroup()->getId(),
                    'group_name' => $usergroup->getGroup()->getName(),
                    'status' => $usergroup->getStatus(),
                    'role' => $usergroup->getRole(),
                    'make_admin_by' => $makeAdminByName,
                );
            }
            return $this->createJsonResponse(OK, SUCCESS_MSG, $arrResult);
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }


    /**
     * @Route("/changeAdminGroup")
     * @Method("POST")
     * @author Phuoc
     */
    public function changeAdminGroupAction(Request $request) {
        $groupId = $request->request->get('group_id');
        $memberId = $request->request->get('member_id');

        $memberRecord = null;
        $adminRecord = null;

        $paramValidator = $this->validateParams([$groupId, $memberId]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Get EntityManager
        $em = $this->getDoctrine()->getManager();

        //Check Group existed
        $group = $em->getRepository('AppBundle:Group')->findOneById($groupId);
        if (empty($group)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.group.INVALID_GROUP'));
        }

        $member = $em->getRepository('AppBundle:User')->findOneById($memberId);
        if (empty($member)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user.INVALID_MEMBER'));
        }

        //Check Member is a member of the group.
        $isGroupMember = $em->getRepository('AppBundle:UserGroup')->isGroupMember($group, $member);
        if ($isGroupMember) { //If Member is a member, System warn user of that.
            $memberRecord = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
                'group' => $group,
                'user' => $member
            ));
            if (!$memberRecord) {
                return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user.INVALID_MEMBER'));
            }
        } else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        }

        //Check User is a member of the group.
        $user = $this->getUser();
        $isGroupAdmin = $em->getRepository('AppBundle:UserGroup')->isGroupMember($group, $user);
        if ($isGroupAdmin) { //If User is a member, System warn user of that.
            if ($isGroupAdmin->getRole()) { //Check user is admin of the group
                $adminRecord = $em->getRepository('AppBundle:UserGroup')->findOneBy(array(
                    'group' => $group,
                    'user' => $user
                ));
                if ($adminRecord) {
                    $adminRecord->setRole(0);//update role = 0

                    $memberRecord->setRole(1);//update role = 1
                    $memberRecord->setMakeAdminBy($user);
                    $em->flush();

                    //send notification

                    $udId = $memberRecord->getUser()->getUdId();
                    if ($udId && $memberRecord->getUser()->getIsRunning()) {
                        $tokens = array();
                        $sms = $adminRecord->getUser()->getName() . "made you the admin of ". $adminRecord->getGroup()->getName() . " group.";
                        $data = array(
                            "alert" => $sms,
                            'type' => "MakeAdmin",
                        );
                        $tokens[] = $udId;

                        $this->sendIOSPN($data, $tokens, true);
                        $this->increaseNotificationCount($memberRecord->getUser()->getId());
                    }


                    return $this->createJsonResponse(OK, SUCCESS_MSG, array());
                } else {
                    return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user.USER_IS_NOT_ADMIN'));
                }
            } else {
                return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.PERMISSION_DENIED'), array());
            }
        } else {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.user_group.IS_NOT_MEMBER'), array());
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }

    /**
     * @Route("/confirmMakeAdminGroup")
     * @Method("POST")
     * @author Phuoc
     */

    public function confirmMakeAdmin(Request $request) {
        $usergroup_id = $request->request->get('usergroup_id');
        $paramValidator = $this->validateParams([$usergroup_id]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        $userGroup = $em->getRepository('AppBundle:UserGroup')->find($usergroup_id);
        if (empty($userGroup)) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.common.INVALID'));
        }

        $userGroup->setMakeAdminBy(null);
        $em->flush();

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }

    /**
     * @Route("/getMainGroup")
     * @Method("POST")
     * @author Phuoc
     */
    public function getMainGroupAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        //Get UserGroups
        $arrUserGroup = null;
        if ($this->getUser()) { //Check user is not empty
            $arrUserGroup = $em->getRepository('AppBundle:UserGroup')->getMainGroupByUser($this->getUser());
        } else {
            throw new \Exception('The User is empty', 401);
        }

        $arrResult = array();
        $nameOfGroup = "";
        foreach ($arrUserGroup as $usergroup) {

            $nameOfGroup = $usergroup->getGroup()->getName();

            $receiver = $usergroup->getDefaultReceiver();
            $receiverName = '';
            $receiverEmail = '';
            $receiverId = '';
            if ($receiver) {
                $receiverId = $receiver->getId();
                $receiverName = $receiver->getName();
                $receiverEmail = $receiver->getEmail();
            }
            $arrResult = array(
                'usergroup_id' => $usergroup->getId(),
                'default_receiver_id' => $receiverId,
                'default_receiver_name' => $receiverName,
                'default_receiver_email' => $receiverEmail,

                'role' => $usergroup->getRole(),
                'is_main' => $usergroup->getIsMain(),
                'is_accept' => $usergroup->getIsAccept(),
                'group_id' => $usergroup->getGroup()->getId(),
                'group_name' => $usergroup->getGroup()->getName(),
            );
            break;
        }

        $arrInvitations = $em->getRepository('AppBundle:UserGroup')->findBy(
            array('email' => $this->getUser()->getEmail())
        );
        if (!empty($arrInvitations)) {
            $udId = $this->getUser()->getUdId();
            if ($udId && $this->getUser()->getIsRunning()) {
                $content = "You are invited to ". $nameOfGroup ." group.";
                if (count($arrInvitations) > 1) {
                    $content = "You are invited to ". count($arrInvitations) . " groups.";
                }
                $tokens = array();
                    $sms = $content;
                    $data = array(
                        "alert" => $sms,
                        'type' => "Request",
                    );
                    $tokens[] = $udId;

                $this->sendIOSPN($data, $tokens, true);
                $this->increaseNotificationCount($this->getUser()->getId());
            }
        }
        return $this->createJsonResponse(OK,SUCCESS_MSG, $arrResult);
    }
}
