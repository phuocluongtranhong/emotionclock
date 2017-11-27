<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Libs\ValueUtil;
use AppBundle\Libs\ConfigUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * @Route("/")
 * @author binh.vt
 */
class LoginController extends AppController {

    /**
     * @Route("/login")
     * @author binh.vt
     */
    public function loginAction(Request $request) {
        $email  = $request->get('email', null);
        $password = $request->get('password', null);
        $udid = $request->get('udid', null);
        $forgotPassword = $request->get('forgot_password', false);

        $paramValidator = $this->validateParams([$email, $password]);
        if ($paramValidator != null) {
            return $paramValidator;
        }
        $user = new User();
        $passwordEncode = $this->get('security.password_encoder')->encodePassword($user, $password);

        $em = $this->getDoctrine()->getManager();

        $tmpUser = $em->getRepository('AppBundle:User')->findOneBy( array('email' => $email) );
        if (!$tmpUser){
            return $this->createJsonResponse(NG,  ValueUtil::constToText('api_message.login.INVALID_USER'));
        }

        $tmpUser = null;
        if (!$forgotPassword) {
            $tmpUser = $em->getRepository('AppBundle:User')->findOneBy(
                array('email' => $email, 'password' => $passwordEncode));
        }
        else {
            $tmpUser = $em->getRepository('AppBundle:User')->findOneBy(
                array('email' => $email, 'passwordTmp' => $passwordEncode));
        }
        if ($tmpUser == null){
            return $this->createJsonResponse(NG,  ValueUtil::constToText('api_message.login.PASSWORD_WRONG'));
        }
        $user = $tmpUser;
        if($udid){
            $user->setUdId($udid);
        }else{
            $user->setUdId('');
        }

        //Create new API_key and save lastest login time.
        $newApiKey = ConfigUtil::createUniqueKey();
        $user->setApiKey($newApiKey);
        $user->setLastLogin(new \DateTime());
        if ($forgotPassword) {
            $user->setPassword($user->getPasswordTmp());
        }
        $user->setPasswordTmp(null);
        $user->setIsRunning(true);
        $em->flush();

        $userResult = array(
            'user_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'udid' => $user->getUdid(),
            'api_key' => $user->getApiKey()
        );

        $arrUserGroup = $em->getRepository('AppBundle:UserGroup')->getMainGroupByUser($user->getId());
        $arrResult = array();
        foreach ($arrUserGroup as $usergroup) {
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

        return $this->createJsonResponse(OK,SUCCESS_MSG, array(
            'user'=>$userResult,
            'main_group'=>$arrResult,
        ));
    }

    /**
     * @Route("/register")
     * @Method("POST")
     * @author binh.vt
     */
    public function registerAction(Request $request) {
        $email          = $request->get('email');
        $password       = $request->get('password');
        $name           = $request->get('name');
        $phone_number   = $request->get('phone_number');
        $udid           = $request->get('udid');

        $paramValidator = $this->validateParams([$email, $password, $name]);
        if ($paramValidator != null) {
            return $paramValidator;
        }

        if (strlen($password) < 8) {
            return $this->createJsonResponse(NG,  ValueUtil::constToText('api_message.login.PASSWORD_SHORT'));
        }

        $em = $this->getDoctrine()->getManager();
        $tmpUser = $em->getRepository('AppBundle:User')->findOneBy( array('email' => $email) );
        if ($tmpUser){
            return $this->createJsonResponse(NG,  ValueUtil::constToText('api_message.register.Email_Exist'));
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $passwordEncode = $this->get('security.password_encoder')->encodePassword($user, $password);
        $user->setPassword($passwordEncode);

        if($phone_number){
            $user->setPhoneNumber($phone_number);
        }
        else {
            $user->setPhoneNumber('');
        }
        if($udid){
            $user->setUdId($udid);
        }else{
            $user->setUdId('');
        }

        //Create new API_key and save lastest login time.
        $newApiKey = ConfigUtil::createUniqueKey();
        $user->setApiKey($newApiKey);
        $user->setLastLogin(new \DateTime());

        $user->setIsRunning(true);
        $em->persist($user);
        $em->flush();

        //push notification when user was added to groups
        $arrInvitations = $em->getRepository('AppBundle:UserGroup')->findBy(
            array('email' => $email)
        );
        if (!empty($arrInvitations)) {
            if ($udid) {
                $content = "You are invited to a group.";
                if (count($arrInvitations) > 1) {
                    $content = "You are invited to ". count($arrInvitations) . " groups.";
                }
                $tokens = array();
                $sms = $content;
                $data = array(
                    "alert" => $sms,
                    'type' => "Request",
                );
                $tokens[] = $udid;

                $this->sendIOSPN($data, $tokens, true);
                $this->increaseNotificationCount($user->getId());
            }
        }

        $array = array(
            'user_id' =>  $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'udid' =>  $user->getUdid(),
            'api_key' =>  $user->getApiKey(),
        );
        return $this->createJsonResponse(OK,SUCCESS_MSG,$array);
    }


    /**
     * @Route("/forgotPassword")
     * @Method("POST")
     * @author binh.vt
     */
    public function forgotPasswordAction(Request $request) {
        $email = $request->request->get('email');
        $paramValidator = $this->validateParams([$email]);
        if (!empty($paramValidator)) {
            return $paramValidator;
        }

        //Get EntityManager.
        $em = $this->getDoctrine()->getManager();

        //Check email is valid.
        $user = $em->getRepository('AppBundle:User')->findOneBy( array('email' => $email) );
        if (empty($user)){
            return $this->createJsonResponse(NG,  ValueUtil::constToText('api_message.change_password.EMAIL_NOT_EXIST'));
        } else {
            //Do nothing.
        }

        //Random new password.
        $newPassword = $this->generateRandomPassword();
        $hashNewPassword = ConfigUtil::passwordHash($this->container,$newPassword);

        //Reset password
        $user->setPasswordTmp($hashNewPassword);
        $em->flush();
        //Send a mail to notify to user.
        $to = $user->getEmail();
        $title = '[Clock-emotional] Your password has been changed';
        $body = $this->renderView('AppBundle:User:forgotPassword_content.html.twig', array(
            'entity' => $user,
            'newPassword' => $newPassword
        ), 'text/html; charset=UTF-8');

        //get mailer.
        $mailer = $this->get('mailer');
        $isSuccessfull = ConfigUtil::sendMail($mailer, $to, $title, $body);

        if (!$isSuccessfull) {
            return $this->createJsonResponse(ERROR_SERVER, ValueUtil::constToText('api_message.common.EMAIL_NOT_SEND'), array());
        } else {
            //Do nothing.
        }

        return $this->createJsonResponse(OK, SUCCESS_MSG, array());
    }

    /**
     * @author binh.vt
     */
    private function generateRandomPassword($length = 10) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }

    /**
     * Automatic Deployment
     * @Route("/gitHook")
     * @author binh.vt
     */
    public function gitHookAction(Request $request) {
        $gitDir = $this->get('kernel')->getRootDir().'../';
        $cacheDir = $gitDir.'app/cache/prod';
        $commands = [
            "cd $gitDir",
            "git pull origin master",
        ];
        foreach ($commands as $command) {
            echo shell_exec($command);
        }

        //Clear cache
        try {
            $fs->remove($cacheDir);
        } catch (IOExceptionInterface $e) {
            echo "Clear Cache Error! ".$e->getPath();
        }
        die;
    }
}
