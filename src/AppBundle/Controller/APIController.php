<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Libs\ValueUtil;
use AppBundle\Libs\ConfigUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\User;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * API controller (Rest API)
 *
 * @Route("/api/")
 * @author Phuoc
 */

class APIController extends AppController
{
    protected $SUCCESS = null;
    protected $SUCCESS_MSG = null;
    private $_enableStatus = 0;

    public function __construct() {
        $this->SUCCESS = ValueUtil::constToValue('api.status_code.SUCCESS');
        $this->SUCCESS_MSG = ValueUtil::constToText('api_message.common.SUCCESS');
        $this->_enableStatus = ValueUtil::constToValue('common.disableFlag.ENABLE');
    }


    /**
     * Create a JSON response for code, message ,data
     * @param $code
     * @param $message
     * @param $data
     * @return JsonResponse
     */
    public function createJsonResponse($code, $message, $data = array())
    {
        $result = array(
            'message' => $message,
            'code' => $code,
            'data' => $data,
        );
        return new JsonResponse($result);
    }

    private function __paramErrorResponse($code = null, $message = null) {
        $code = !empty($code) ? $code : ValueUtil::constToValue('api.status_code.MISSING_PARAMETER');
        $message = !empty($message) ? $message : ValueUtil::constToValue('api_message.common.MISSING_PARAMETER');

        $result = array(
            'message' => $message,
            'code' => $code,
            'data' => array(),
        );
        return new JsonResponse($result);
    }
}
