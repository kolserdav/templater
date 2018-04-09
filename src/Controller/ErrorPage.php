<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 27.03.2018
 * Time: 0:29
 */

namespace Avir\Templater\Controller;


class ErrorPage
{
   public function errorPage(){
       echo 'This is a page: 404';
       return false;
   }
}