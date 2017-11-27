<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/13/14
 * Time: 19:38
 */

namespace AppBundle\Libs;


use Exception;

class StringUtil {

  public static function contains($haystack, $needle)
  {
    if (strpos($haystack, $needle) === false) { // not found
      return false;
    } else {
      return true;
    }
  }

  public static function startsWith($haystack, $needle)
  {
    return !strncmp($haystack, $needle, strlen($needle));
  }

  public static function endsWith($haystack, $needle)
  {
    $length = strlen($needle);
    if ($length == 0) {
      return true;
    }

    return (substr($haystack, -$length) === $needle);
  }

  public static function formatCode($num, $length)
  {
    return str_pad($num, $length, '0', STR_PAD_LEFT);
  }

  /**
   * Generate a random string
   * @author toanlm 2014/10/14
   * Examples
   * str_rand() => m2dy5ofe
   * str_rand(15) => remdjynd47b66hq
   * str_rand(15, 'numeric') => 504359393089603
   * str_rand(15, '01') => 111001110111101
   */
  public static function strRand($length = null, $output = null) {
    // Possible seeds
    $outputs['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
    $outputs['numeric'] = '0123456789';
    $outputs['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
    $outputs['hexadec'] = '0123456789abcdef';

    // Choose seed
    if (isset($outputs[$output])) {
      $output = $outputs[$output];
    }

    // Seed generator
    list($usec, $sec) = explode(' ', microtime());
    $seed = (float) $sec + ((float) $usec * 100000);
    mt_srand($seed);

    // Generate string
    $str = '';
    $output_count = strlen($output);
    for ($i = 0; $length > $i; $i++) {
      $str .= $output{mt_rand(0, $output_count - 1)};
    }

    return $str;
  }

  /**
   * Truncate a string with length
   * @author khiemnd 2013/08/27
   * @return string
   */
  public static function truncate($str, $length, $suffix = '')
  {
    if (mb_strlen($str, 'UTF-8') > $length) {
      $str = mb_substr($str, 0, $length, 'UTF-8') . $suffix;
    }

    return $str;
  }

  /**
   * Cut a string with length, return sub-string which cut
   * @author toanlm 2014/10/14
   * @return string
   */
  public static function cutString(&$str, $length)
  {
    $sub = mb_substr($str, 0, $length, 'UTF-8');
    $str = mb_substr($str, $length, mb_strlen($str, 'UTF-8') - $length, 'UTF-8');

    return $sub;
  }

  /**
   * Convert the Currency to Number (remove comma)
   * @author toanlm 2014/11/14
   * @return string
   */
  public static function currencyToNumber($value)
  {
    $value = str_replace(',', '', $value);

    if ($value && !is_numeric($value)) {
      throw new WrongArgumentException(ConfigUtil::getMessage('interface.common.number_value_not_valid', array('value' => $value)));
    }

    return $value;
  }

  /**
   * Convert Number to Currency
   * @param Integer $number
   * @return string
   *
   * @author Khiemnd 2012/12/05
   */
  public static function numberToCurrency($number, $divide = 0)
  {
    if (isset($number) && $number !== '') {
      if ($divide > 0) {
        $number = round($number/(str_pad(1, ($divide + 1), 0, STR_PAD_RIGHT)));
      }

      return number_format($number);
    }

    return '';
  }

  public static function dbFieldName($field)
  {
    $dbElements = array();
    $fieldElements = explode('_', $field );
    foreach($fieldElements as $key=>$element){
      if($key > 0){
        $element =  ucfirst($element);
      }
      $dbElements[] = $element;
    }

    return implode('', $dbElements);
  }

  public static function formatDate($str){
    try {
      $date = new \DateTime($str);
    } catch (Exception $e) {
      return null;
    }
    return $date->format(ConfigUtil::get('date_format'));
  }
} 