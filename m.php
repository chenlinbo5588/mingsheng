<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal.php 33234 2013-05-08 04:13:19Z andyzheng $
 */

define('APPTYPEID', 4);
define('CURSCRIPT', 'm');

require './source/class/class_core.php';
$discuz = C::app();

$cachelist = array('userapp', 'portalcategory', 'diytemplatenameportal');
$discuz->cachelist = $cachelist;
$discuz->init();

require DISCUZ_ROOT.'./source/function/function_home.php';

if(empty($_GET['mod']) || !in_array($_GET['mod'], array('mobile','mobile_list','mobile_view','mobile_search','mobile_psearch'))) $_GET['mod'] = 'mobile';


define('CURMODULE', $_GET['mod']);
runhooks();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['portal']);
$_G['disabledwidthauto'] = 1;
require_once libfile('portal/'.$_GET['mod'], 'module');

?>