<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/13/14
 * Time: 19:37
 */

namespace AppBundle\Libs;

use AppBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;
use VMelnik\DoctrineEncryptBundle\Encryptors\AES256Encryptor;

class ConfigUtil {

  const PATH = 'Resources/config/';
  const CONFIG_DIR = 'Config';
  const VALUE_LIST_DIR = 'Value';
  const MESSAGE_DIR = 'Message';
  const CSV_TEMPLATE_DIR = 'CSV';

  /**
   * Get root path
   * @return string ~/DemoBundle/
   */
  public static function rootPath()
  {
    return __DIR__.'/../';
  }

  /**
   * Get upload path
   * @return string ~/data/
   */
  public static function uploadPath()
  {
    return self::rootPath() . '../../../data/';
  }

  /**
   * Get message from message_file, params is optional
   * @param $key
   * @param array $paramArray
   * @return mixed|null
   */
  public static function getMessage($key, $paramArray = array())
  {
    $message = self::getConfig(self::MESSAGE_DIR, $key);

    if ($message && is_string($message)) {
      foreach ($paramArray as $param => $value) {
        $message = str_replace(sprintf('{{ %s }}', $param), $value, $message);
      }
    }

    return $message;
  }

  /**
   * Get $key value from common config file
   * @param $key
   * @return null
   */
  public static function get($key)
  {
    return self::getConfig(self::CONFIG_DIR, $key);
  }

  /**
   * Get $key value from value_list_file
   * @param $keys
   * @param array $options
   * @return array|null
   */
  public static function getValueList($keys, $options = array())
  {
    $keys = explode('.', $keys);

    if (!is_array($keys) || count($keys) != 2) return NULL;

    $exclude = FALSE;

    if (isset($options['exclude'])) {
      $exclude = str_replace(' ', '', $options['exclude']);
      $exclude = explode(',', $exclude);
    }

    list($fileName, $param) = $keys;

    $valueList = self::loadValueList($fileName, $param);

    if ($valueList && is_array($valueList)) {
      $resultList = array();

      foreach ($valueList as $key => $value) {
        $value = explode('|', $value);

        if (!$exclude || !isset($value[1]) || !in_array($value[1], $exclude)) {
          $resultList[$key] = $value[0];
        }
      }

      return $resultList;
    }

    return NULL;
  }

  /**
   * @param $keys
   * @param bool $getText
   * @return int|null|string
   */
  public static function getValue($keys, $getText = FALSE)
  {
    $keys = explode('.', $keys);

    if (!is_array($keys) || count($keys) != 3) return NULL;
    list($fileName, $key, $const) = $keys;
    $valueList = self::loadValueList($fileName, $key);
    if ($valueList && is_array($valueList)) {
      foreach ($valueList as $key => $value) {
        $value = explode('|', $value);
        if (isset($value[1]) && $value[1] == $const) {
          if ($getText) return $value[0];

          return $key;
        }
      }
    }

    return NULL;
  }

  /**
   * Load $key value from specific value_list_file
   * @param $fileName
   * @param $key
   * @return mixed
   */
  public static function loadValueList($fileName, $key)
  {
    global $cacheYaml;
    global $cacheValueList;

    if(!isset($cacheYaml)) $cacheYaml = array();
    if(!isset($cacheValueList)) $cacheValueList = array();

    $valueListKey = $fileName . '.' . $key;
    if (isset($cacheValueList[$valueListKey])) {
      // Retreiving from local static cache
      return $cacheValueList[$valueListKey];
    }

    if (isset($cacheYaml[$fileName])) {
      // Retreiving from local static cache
      $paramValue = $cacheYaml[$fileName];
    } else {
      $filePath = self::rootPath() . self::PATH . self::VALUE_LIST_DIR . '/' . $fileName. '.yml';
      $paramValue = Yaml::parse($filePath);
      $cacheYaml[$fileName] = $paramValue; // cache
    }

    $cacheValueList[$valueListKey] = $paramValue[$key]; // cache
    return $paramValue[$key];
  }

  /**
   * Get config params from DemoBundle/Reosurce/config/folder_name
   * @param $folderName
   * @param $paramKey
   * @return null
   */
  private static function getConfig($folderName, $paramKey)
  {
    global $cacheConfig;
    global $cacheConfigFile;

    if(!isset($cacheConfig)) $cacheConfig = array();
    if(!isset($cacheConfigFile)) $cacheConfigFile = array();

    if (isset($cacheConfig[$paramKey])) {
      return $cacheConfig[$paramKey];
    }

    $folderPath = self::rootPath() . self::PATH . $folderName;
    $paramKeyArr = explode('.', $paramKey);

    foreach (glob($folderPath.'/*.yml') as $yamlSrc) {

      if (isset($cacheConfigFile[basename($yamlSrc)])) {
        $paramValue = $cacheConfigFile[basename($yamlSrc)];
      } else {
        $paramValue = Yaml::parse($yamlSrc);
        $cacheConfigFile[basename($yamlSrc)] = $paramValue;
      }

      $found = TRUE;

      foreach ($paramKeyArr as $key) {
        if (!isset($paramValue[$key])) {
          $found = FALSE;
          break;
        }

        $paramValue = $paramValue[$key];
      }

      if ($found) {
        $cacheConfig[$paramKey] = $paramValue;
        return $paramValue;
      }
    }

    return NULL;
  }


  public static function getCSVformat($fileName)
  {
    $fileName = self::rootPath() . 'Resources/config/CSV/' . $fileName . '.csv.yml';

    return Yaml::parse($fileName);
  }

  /**
   * AES256Encrypt a string
   * @param  Container $container
   * @param string $str
   * @return string
   */
  public static function encrypt($str){
    $secret = self::get('encrypt_key');
    $AES256Encryptor = new AES256Encryptor($secret);

    return  $AES256Encryptor->encrypt($str);
  }

  /**
   * AES256Encrypt a string
   * @param  Container $container
   * @param string $str
   * @return string
   */
  public static function decrypt($str){

    $secret = self::get('encrypt_key');
    $AES256Encryptor = new AES256Encryptor($secret);

    return  $AES256Encryptor->decrypt($str);
  }
  /**
   * Generate a password for User from plain
   * @param Container $container
   * @param string $plainPassword
   * @return string
   */
  public static function passwordHash($container, $plainPassword ){
    $factory = $container->get('security.encoder_factory'); // Get Encoder factory

    // Get encoder of User entity
    $user = new User();
    $encoder = $factory->getEncoder($user);

    // Generate passowrd
    $password = $encoder->encodePassword($plainPassword, $user->getSalt());

    return $password;
  }

  /**
   * @param Mailer $mailer Use can take it from controller ($this->get('mailer'))
   */

  public static function sendMail($mailer, $to, $title = null, $body = null) {
      $message = \Swift_Message::newInstance();
      $message->setSubject($title)
                ->setFrom('luongthphuoc@gmail.com')
                ->setTo($to)
                ->setBody($body)
                ->setContentType('text/html; charset=UTF-8');
      return $mailer->send($message);

  }

  public static function toTime($fromTime, $helpGenre ){
    //toTime = fromTime + helpGenre.time
    $minutes = explode(':', $fromTime)[1];
    $toTime = (int)$fromTime + $helpGenre;
    if($toTime > 24){
      $temp = (int)($toTime/24);
      $toTime = $toTime - 24*$temp;
    }
    $toTime = date("H:s" ,  strtotime( $toTime .':00:' . $minutes));
    return $toTime;
  }

  /**
   * get client of IP
   * @return [type] [description]
   */
  public static function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
  }

  public static function createUniqueKey($secretKey = "ItIsNotSecretKey") {
    return md5(microtime().rand().$secretKey);
  }
}
