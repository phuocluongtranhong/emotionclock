<?php
/**
 * Created by PhpStorm.
 * User: briswell02
 * Date: 11/13/14
 * Time: 19:39
 */

namespace AppBundle\Libs;


class ValueUtil {

  /**
   * Get value list from yml config file
   * @param $keys
   * @param array $options
   * @return array|null
   */
  public static function get($keys, $options = array())
  {
    return ConfigUtil::getValueList($keys, $options);
  }

  /**
   * Get Text from value (in Yml config file)
   * @param $keys
   * @param $value
   * @param null $default
   * @return null
   */
  public static function valueToText($keys, $value, $default = NULL)
  {
    $valueList = self::get($keys);
    if (!isset($valueList[$value])) return $default;
    return $valueList[$value];
  }

  /**
   * Get value from const (in Yml config file)
   * @param $keys
   * @return int|null|string
   */
  public static function constToValue($keys)
  {
    return ConfigUtil::getValue($keys);
  }

  /**
   * Get text from const (in Yml config file)
   * @param $keys
   * @return int|null|string
   */
  public static function constToText($keys)
  {
    return ConfigUtil::getValue($keys, TRUE);
  }

  /**
   * Get value from test i
   * @param $searchText
   * @param $keys
   * @return int|null|string
   */
  public static function textToValue($searchText, $keys)
  {
    $valueList = ValueUtil::get($keys);

    foreach ($valueList as $key => $text) {
      if ($searchText == $text) {
        return $key;
      }
    }

    return NULL;
  }

  /**
   * @param $array
   * @param $key
   * @return array
   */
  public static function getArrayValue($array, $key)
  {
    $results = array();
    foreach ($array as $data) {
      $results[] = $data[$key];
    }

    return $results;
  }
} 