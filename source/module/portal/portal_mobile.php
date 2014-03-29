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

if(isset($_G['makehtml'])){
	helper_makehtml::portal_index();
}
$allposts = 0;
$allmembers = 0;
$nowdeals = DB::result_first("SELECT count(*) AS totalreplies FROM ".DB::table('forum_thread').' WHERE displayorder >= 0 AND sortid = 3 ');;
$allreplies = 0;
$allposts = DB::result_first("SELECT SUM(posts) AS totalposts FROM ".DB::table('forum_forum'));
$allmembers = DB::result_first("SELECT count(*) FROM ".DB::table('common_member'));
$allreplies = DB::result_first("SELECT SUM(replies) AS totalreplies FROM ".DB::table('forum_thread'));

/**
 *  
 * 
 * 网友正在问取[未受理][已受理]，最新15条
 * 部门正在答取[已回复]，最新15条
 *  
 */
loadcache('forums');



require './source/function/function_forum.php';


$bm_count = 0;
foreach($_G['cache']['forums'] as $key => $val){
    if($val['type'] == 'group'){
        continue;
    }
    $bm_count++;
}

$askingThreads = C::t('forum_thread')->fetch_by_sortid(array(2,3), " dateline DESC " ,0,15);
$answeringThreads = C::t('forum_thread')->fetch_by_sortid(4," lastpost DESC " ,0,15);

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

$askingThreads = thread_add_icon_by_row($askingThreads);
$answeringThreads = thread_add_icon_by_row($answeringThreads);

include_once template('index');
?>