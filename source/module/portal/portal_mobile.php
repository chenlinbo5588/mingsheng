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
/*
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
*/
$fids = array();
foreach($_G['cache']['forums'] as $fid => $forum) {
    //获取可用板块
    if($forum['type'] != 'group' && $forum['status'] > 0 && !$forum['viewperm'] && !$forum['havepassword']) {
        $fids[] = $fid;
    }
}

$typeList = array();
$types = array();
$threadsList = array();
$lang = lang('forum/template');

if($fids){
    $typeList = C::t('forum_threadclass')->fetch_all_by_fid($fids);
    foreach($typeList as $v){
        if($v['name'] == $lang['guide_zxqz']){
            $types[0][] = $v['typeid'];
        }elseif($v['name'] == $lang['guide_tsjb']){
            $types[1][] = $v['typeid'];
        }elseif($v['name'] == $lang['guide_jyxc']){
            $types[2][] = $v['typeid'];
        }else{
            $types[3][] = $v['typeid'];
        }
    }
    
    foreach($types as $v){
        $threadsList[] = C::t('forum_thread')->fetch_all_by_typeid($v,0,5);
    }
}

foreach($threadsList as $k => $v){
    $threadsList[$k] = thread_add_icon_by_row($v);
}


$wheresql = 'catid = 2 AND status=0 ';
$list = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY at.dateline DESC', 0, 10, 0, 'at');

$wheresql = 'catid = 20 AND status=0 ';
$zwlist = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY at.dateline DESC', 0, 10, 0, 'at');

include_once template('index');


?>