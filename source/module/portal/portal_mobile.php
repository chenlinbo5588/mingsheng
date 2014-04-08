<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_index.php 31313 2012-08-10 03:51:03Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting('portal');
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][1]['navname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][1]['navname'];
}
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][1]['navname'];
}




loadcache('forums');



require './source/function/function_forum.php';


$bm_count = 0;
foreach($_G['cache']['forums'] as $key => $val){
    if($val['type'] == 'group'){
        continue;
    }
    $bm_count++;
}

if(function_exists('curl_init')){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://www.weather.com.cn/data/sk/101210403.html');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, false);

    $resp = curl_exec($ch);
    curl_close($ch);

}else{
    $resp = file_get_contents("http://www.weather.com.cn/data/sk/101210403.html");
}

$weather = $resp ? json_decode($resp,true) : '';

$announcement = array();

if($_G['page'] == 1) {
    $announcement = C::t('forum_announcement')->fetch_by_displayorder(TIMESTAMP);
    if($announcement){
        $announcement['starttime'] = dgmdate($announcement['starttime'], 'u');
    }
}

/**
 *  
 * 
 * 网友正在问取[未受理][已受理]，最新15条
 * 部门正在答取[已回复]，最新15条
 *  
 */

$askingThreads = C::t('forum_thread')->fetch_by_sortid(array(2,3), " dateline DESC " ,0,10);
$answeringThreads = C::t('forum_thread')->fetch_by_sortid(4," lastpost DESC " ,0,10);

$zhThreads =  C::t('forum_thread')->fetch_all_search(array(
    'displayorder' => array(3,2,1,0)
),0,0,30,'replies');

$lang = lang('forum/template');

foreach($askingThreads as $k => $val) {
    $val['dbdateline'] = $val['dateline'];
    $val['dateline'] = date("Y-m-d",$val['dateline']);
    $askingThreads[$k] = $val;
    
}

foreach($answeringThreads as $k =>  $val) {
    $val['dbdateline'] = $val['dateline'];
    $val['dateline'] = date("Y-m-d",$val['dateline']);
    
    $answeringThreads[$k] = $val;
}

/*
foreach($zhThreads as $k =>  $val) {
    $val['dbdateline'] = $val['dateline'];
    $val['dateline'] = date("Y-m-d",$val['dateline']);
    
    $zhThreads[$k] = $val;
}
 */

$askingThreads = thread_add_icon_by_row($askingThreads);
$answeringThreads = thread_add_icon_by_row($answeringThreads);

$wheresql = 'catid = 2 AND status=0 ';
$list = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY at.dateline DESC', 0, 10, 0, 'at');

include_once template('index');


?>