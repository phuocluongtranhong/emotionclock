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

/**
 * API controller (Rest API)
 * @Route("/api/v1/user")
 * @author phuoc
 */

class UserController extends AppController
{

    /**
     * @Route("/updateUdid")
     * Method("POST")
     */
    public function updateUdidAction(Request $request)
    {
        $udid = $request->get('udid', null);
        if ($udid) {
            $user = $this->getUser();
            $user->setUdId($udid);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }
        return $this->createJsonResponse(OK,SUCCESS_MSG,array());
    }

    /**
     * @Route("/getUserInfo")
     * Method("POST")
     */
    public function getUserById(Request $request)
    {
        //the user provider has transform from api_key to user object.
        $user = $this->getUser();

        $array = array(
            'user_id' =>  $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'udid' => $user->getUdid(),
            'api_key' => $user->getApiKey()
        );
        return $this->createJsonResponse(OK,SUCCESS_MSG,$array);
    }

    /**
     * @Route("/updateUserInfo")
     * Method("POST")
     */
    public function updateUserInfo(Request $request)
    {
        $name = $request->get('name');
        $phone_number = $request->get('phone_number');

        $paramValidator = $this->validateParams([$name, $phone_number]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        $em = $this->getDoctrine()->getManager();

        //Get User.
        $user = $this->getUser();

        if($name){
            $user->setName($name);
        }
        if ($phone_number) {
            $user->setPhoneNumber($phone_number);
        }

        $em->flush();
        $array = array(
            'user_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'udid' => $user->getUdid(),
            'api_key' => $user->getApiKey()
        );
        return $this->createJsonResponse(OK,SUCCESS_MSG,$array);
    }


    /**
     * @Route("/changePassword")
     * Method("POST")
     */
    public function changePassword(Request $request)
    {
        $password = $request->get('password');
        $currentPassword = $request->get('current_password');

        $paramValidator = $this->validateParams([$password, $currentPassword]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        //Check new password.
        if (strlen($password) < 8) {
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.login.PASSWORD_SHORT'));
        } else {
            //Do nothing.
        }
        $hashPassword = ConfigUtil::passwordHash($this->container, $password);

        $user = $this->getUser();
        $currentPassword = ConfigUtil::passwordHash($this->container, $currentPassword);
        $passwordInDatabase = $user->getPassword();

        if (!($currentPassword == $passwordInDatabase)) { //Check currentPassword.
            return $this->createJsonResponse(NG, ValueUtil::constToText('api_message.login.OLD_PASSWORD_WRONG'));
        } else {
            $user->setPassword($hashPassword);
            //get Eintity Manager;
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $array = array(
            'user_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'udid' => $user->getUdid(),
            'api_key' => $user->getApiKey()
        );
        return $this->createJsonResponse(OK,SUCCESS_MSG,$array);
    }

    /**
     * @Route("/logout")
     * @Method("POST")
     * @author Phuoc
     */
    public function logoutAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->setIsRunning(false);
        $em->persist($user);
        $em->flush();
        return $this->createJsonResponse(OK,SUCCESS_MSG, array(
        ));
    }


    /**
     * @Route("/resetNotificationCount")
     * @Method("POST")
     * @author Phuoc
     */
    public function resetNotificationCount() {
        $em = $this->getDoctrine()->getManager();
        $this->getUser()->setNotificationCount(0);
        $em->persist($this->getUser());
        $em->flush();
        return $this->createJsonResponse(OK,SUCCESS_MSG,array());
    }

    /**
     * @Route("/getNotificationCount")
     * @Method("POST")
     * @author Phuoc
     */
    public function getNotificationCount() {
        $count = $this->getUser()->getNotificationCount();
        $array = array(
            'notification_count' =>  $count,
        );
        return $this->createJsonResponse(OK,SUCCESS_MSG,$array);
    }
    //// ------------------------------------------------------ ////
    //// ------------------ Private methods ------------------- ////
    //// ------------------------------------------------------ ////



}
