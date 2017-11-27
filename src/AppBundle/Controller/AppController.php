<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/27/14
 * Time: 17:25
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Libs\ValueUtil;

// define for api
define('OK', 200);
define('NG', 400);
define('ERROR_SERVER', 500);
define('UNCORRECT_PARAM', 600);
define('SUCCESS_MSG', ValueUtil::constToText('api_message.common.SUCCESS'));
define('UNCORRECT_PARAM_MSG', ValueUtil::constToText('api_message.common.PARAMETER_NOT_CORRECT'));
define('LIMIT_RECORD', 10);
define('TIME_FORMAT', 'Y-m-d H:i:s');
define('DATE_FORMAT', 'Y-m-d');
define('LengthToken', 200);


class AppController extends Controller{

    public function getEntityService()
    {
        return $this->get('entity_service');
    }

    public function validateParams($params){
        foreach($params as $param){
            if($param == null){
                return $this->createJsonResponse(ValueUtil::constToValue('api.status_code.MISSING_PARAMETER'),  ValueUtil::constToText('api_message.common.MISSING_PARAMETER'));
            }
        }
        return null;
    }

    public function createJsonResponse($code, $message, $data = array())
    {
        $result = array(
            'message' => $message,
            'code' => $code,
            'data' => $data,
        );
        return new JsonResponse($result);
    }

    public function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    public function getToken($length = LengthToken)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }
        return $token;
    }

    /***
     * @param $email
     * @param $token
     * @return mixed
     */
    public function isValidUserWithEmail($email, $token) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(
            array('email' => $email, 'token' => $token)
        );
        return $user;
    }

    /**
     * @param $id
     * @param $token
     * @return mixed
     */
    public  function isValidUserWithId($id, $token) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(
            array('id' => $id, 'token' => $token)
        );
        return $user;
    }

    /***
     * @param $email
     * @return int|null|string|void
     */
    function validateEmail($email) {
        $constraintsOpts = array(
            new NotBlank(),
            new Email()
        );

        $errorList = $this->get('validator')->validateValue($email, $constraintsOpts);
        if (count($errorList) > 0) {
            $message = ValueUtil::constToText('api_message.change_password.NOT_VALID_EMAIL');
            return $message;
        }
        return;
    }



    public function sendIOSPN($data, $deviceTokens = array(), $production = false)
    {
        $gateway = 'ssl://gateway.push.apple.com:2195';
        $pemPath = '@AppBundle/Resources/key/apn_pro.pem';

//        if ($this->getUser()->getId() == 1 || $this->getUser()->getId() == 2) {
//            $gateway = 'ssl://gateway.sandbox.push.apple.com:2195';
//            $pemPath = '@AppBundle/Resources/key/apn_dev.pem';
//        }
        $passphrase = '';

        ////////////////////////////////////////////////////////////////////////////////

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->container->get('kernel')->locateResource($pemPath));
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server

        $fp = stream_socket_client(
            $gateway, $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
//            exit("Failed to connect: $err $errstr" . PHP_EOL);
            fclose($fp);
            return;
        }

        // Create the payload body
        $alert = $data['alert'];
        $type = $data['type'];
        $body['aps'] = array(
            "alert" => $alert,
            'sound' => 'default',
            "badge" => 1,
            'data' => array(
                'type' => $type,
                'message' => 1,
            )
        );

        // Encode the payload as JSON
        $payload = json_encode($body);
        foreach ($deviceTokens as $token) {
            $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
            $result = fwrite($fp, $msg, strlen($msg));
            if (!$result) {
//                printf("Message not delivered");
//                echo 'Message not delivered' . PHP_EOL;
            }
            else {
//                echo 'Message successfully delivered' . PHP_EOL;
//                printf("Message delivered");
            }
            flush();
        }
        // Close the connection to the server
        fclose($fp);
    }


    public function increaseNotificationCount($userId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(
            array('id' => $userId)
        );

        $notificationCount = $user->getNotificationCount() + 1;
        $user->setNotificationCount($notificationCount);
        $em->persist($user);
        $em->flush();
    }
}
