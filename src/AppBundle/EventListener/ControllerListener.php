<?php

namespace Bris\SharpBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Libs\ConfigUtil;

class ControllerListener {

	protected $container;
	protected $logger;

	public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

  	public function onKernelController(FilterControllerEvent $event) {
  		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
  			return;
  		}

      	$controllers = $event->getController();
      	if (is_array($controllers)) {
        	$controller = $controllers[0];

        	if (is_object($controller) && method_exists($controller, 'beforeFilter')) {
          		$controller->beforeFilter();
        	}
      	}

      	$this->_logger = new \Doctrine\DBAL\Logging\DebugStack();
        
        $this->container
            ->get('doctrine')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger($this->_logger);
  	}

  	public function onKernelResponse(FilterResponseEvent $event)
	{
	    if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
	    	return;
		}	    	

		$event->getResponse()->headers->set('x-frame-options', 'deny');

		$token = $this->container->get('security.context')->getToken();
		if (!$token) {
			return;
		}

		try {
			$user = $token->getUser();
			if (!$user || $user == "anon.") {
				$userId = null;
			}else {
				$userId = $user->getId();
			}
		
		}catch(\Exception $e) {
			$userId = null;
		}

		$log = array(
			'user_id' => $userId,
			'access_time' => date('Y-m-d H:i:s'),
			'access_ip' => ConfigUtil::getClientIp(),
			'URI' => $event->getRequest()->getRequestUri(),
			'queries' => !empty($this->_logger->queries) ? ($this->_logger->queries) : array()
		);

		$this->container->get('special_logger')->info('action_log', $log);
	}
}
?>