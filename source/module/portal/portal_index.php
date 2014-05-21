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

if($_G['uid']){
    $noticeCount = C::t('home_notification')->count_by_uid($_G['uid'], 1, 'system');
}


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

include_once template('diy:portal/index');
?>