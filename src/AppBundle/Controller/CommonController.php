<?php

namespace AppBundle\Controller;

use AppBundle\Libs\ConfigUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * User controller.
 *
 * @Route("/Common")
 *
 */
class CommonController extends Controller
{
  const NAME_SPACE = 'AppBundle';
  /**
   * @Route("/generateEntity")
   * @Template()
   */
  public function generateEntityAction()
  {
    $form = $this->createFormBuilder()
      ->add('submitFile', 'file', array('label' => 'File CSV '))
      ->getForm();

    $request = $this->get('request');
    // Check if we are posting stuff
    if ($request->getMethod('post') == 'POST') {
      // Bind request to the form
      //$form->bindRequest($request);
      $form->handleRequest($request);

      // If form is valid
      if ($form->isValid()) {
        // Get file
        $submitFile = $form->get('submitFile');
        // Your csv file here when you hit submit button
        $file = $submitFile->getData();
        $fileName = $file->getClientOriginalName();
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));
        $tableName = $fileName;
        $fileName = ucwords(str_replace('_',' ', $fileName));
        $fileName = str_replace(' ','', $fileName);
        $data = $file->openFile('r');

        $fields = array();
        $is_header = true;
        while (!$data->eof()) {
          $arr = $data->fgetcsv(",");
          mb_convert_variables('utf-8', 'sjis-win', $arr);     //Change Encoding to UTF-8
          if ($is_header) {
            $is_header = false;
            continue;
          }

          if (count($arr) >= 8) {
            foreach($arr as $key => $item) {
              $arr[$key] = trim($item);
            }

            if ($arr[2] === 'id') {
              continue;
            }

            $validator = strtolower($arr[2]);
            if (strpos($validator,'url') !== false) {
              $arr['validator'] = 'Url()';
            } elseif (strpos($validator,'email') !== false) {
              $arr['validator'] = 'Email()';
            }

             if ($arr[3] == 'decimal') {
               $arr[7] = str_replace('(', '', $arr[7]);
               $arr[7] = str_replace(')', '', $arr[7]);
               $length = explode(",", $arr[7]);
               $arr['precision'] = 0;
               $arr['scale'] = 0;
               if (count($length) == 2) {
                 $arr['precision'] = trim($length[0]);
                 $arr['scale'] = trim($length[1]);
               } else {
                 $arr['precision'] =  $arr[7];
               }
             }

            if ($arr[8] == '1:N') {
              $arr['relation'] = 'ManyToOne';
            } elseif ($arr[8] == '1:1') {
              $arr['relation'] = 'OneToOne';
            }

            if ($arr[9]) {
              $arr['refTable'] = str_replace(' ','', ucwords(str_replace('_',' ', $arr[9])));
            }

            if (isset($arr['relation'])) {
              $arr[3] = '';
            }

            $fieldName = ucwords(str_replace('_',' ', $arr[2]));
            $fieldName = str_replace(' ','', $fieldName);
            $fieldName = lcfirst($fieldName);

            if (isset($arr['relation']) && \AppBundle\Libs\StringUtil::endsWith($fieldName, 'Id')) {
              $fieldName = substr($fieldName , 0, -2);
            }

            $arr['fieldName'] = $fieldName;

            $fields[] = $arr;
          }
        }

        $response = $this->render('AppBundle:Common:exportEntity.html.twig', array('fields' => $fields,'fileName' => $fileName, 'tableName' => $tableName, 'nameSpace' => self::NAME_SPACE));
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName.'.php');
        return $response;
      }
    }

    return array('form' => $form->createView());
  }

  /**
   * @Route("/generateCSVFormat")
   * @Template()
   */
  public function generateCSVFormatAction()
  {
    $form = $this->createFormBuilder()
      ->add('submitFile', 'file', array('label' => 'File CSV '))
      ->getForm();

    $request = $this->get('request');
    // Check if we are posting stuff
    if ($request->getMethod('post') == 'POST') {
      // Bind request to the form
      $form->bindRequest($request);

      // If form is valid
      if ($form->isValid()) {
        // Get file
        $submitFile = $form->get('submitFile');
        // Your csv file here when you hit submit button
        $file = $submitFile->getData();
        $fileName = $file->getClientOriginalName();
        $fileName = $fileName . '.yml';
        $data = $file->openFile('r');

        $fields = array();

        while (!$data->eof()) {
          $arr = $data->fgetcsv();
          $field = array();

          if (count($arr) >= 6) {
            $field['field_label'] = mb_convert_encoding($arr[0], 'UTF-8', 'EUC-JP,SJIS');
            $field['key'] = $arr[1];
            $arr[1] = ucwords(str_replace('_',' ', $arr[1]));
            $arr[1] = lcfirst(str_replace(' ','', $arr[1]));
            $field['field'] = preg_replace('/(Id)$/', '', $arr[1]);
            $field['required'] = $arr[2];
            $field['type'] = $arr[3];
            $field['selector'] = $arr[4];
            $field['class'] = $arr[5];
            if (isset($arr[6])) {
              $field['selector_label'] = mb_convert_encoding($arr[6], 'UTF-8', 'EUC-JP,SJIS');
            }
            array_push($fields, $field);
          }

        }

        $response = $this->render('AppBundle:Common:csvFormat.html.twig', array('fields' => $fields));
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName);

        return $response;
      }
    }

    return array('form' => $form->createView());
  }

  /**
   * @Route("/getAddressByPostal.json", name="Common_getAddressByPostal")
   */
  public function getAddressByPostalAction()
  {
    $getUrl = Config::get('postal_url');
    $params = $this->getRequest()->get('params', array());
    $data = array();

    if (isset($params['zipCode']) && $params['zipCode']) {
      $getUrl .= '?zn=' . str_replace('-', '', $params['zipCode']);

      $ch = curl_init($getUrl);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $result = curl_exec($ch);
      curl_close($ch);

      $csvData = str_getcsv($result);

      if (isset($csvData[12])) {
        $prefectureName = mb_convert_encoding($csvData[12], 'UTF-8', 'EUC-JP');

        $prefecture = ValueList::get('common.prefecture');

        foreach ($prefecture as $key => $value) {
          if ($prefectureName == $value) {
            $data['prefecture'] = $key;
            break;
          }
        }
      }

      if (isset($csvData[13])) {
        $data['city'] = mb_convert_encoding($csvData[13], 'UTF-8', 'EUC-JP');
      }

      if (isset($csvData[14])) {
        $data['address1'] = mb_convert_encoding($csvData[14], 'UTF-8', 'EUC-JP');
      }
    }

    return $this->getJsonResponse($data);
  }

  /**
   * @Route("/JS/{page}.js", name="Common_pageJS")
   */
  public function pageJsAction($page)
  {
    $jsFile = 'PS2IjnetBundle:JS:' . $page .'.js.twig';

    $rendered = $this->renderView($jsFile);
    $response = new Response($rendered);
    $response->headers->set( 'Content-Type', 'text/javascript' );

    return $response;
  }

}
