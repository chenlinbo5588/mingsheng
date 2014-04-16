<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_guide.php 34066 2013-09-27 08:36:09Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');
$lang = lang('forum/template');

//print_r($_G['setting']['bbname']);
$navtitle = $_G['setting']['bbname'] ;
$perpage = 15;
$start = $perpage * ($_G['page'] - 1);


loadcache('forums');
	
foreach($_G['cache']['forums'] as $fid => $forum) {
    //获取可用板块
    if($forum['type'] != 'group' && $forum['status'] > 0 && !$forum['viewperm'] && !$forum['havepassword']) {
        $fids[] = $fid;
    }
}

$data = get_list($fids, $start, $perpage);
$totalPage = ceil($data['count']/$perpage) ;
//echo $totalPage;

if($_G['page'] > $totalPage){
    $_G['page'] = $totalPage;
}

if($_G['page'] == 1){
    $prevPage = 1;
}else{
    $prevPage = $_G['page'] -1;
}

if($_G['page'] == $totalPage){
    $nextPage = $totalPage;
}else{
    $nextPage = $_G['page'] + 1;
}



loadcache('stamps');

//print_r($data);

//include template('forum/guide');
include template('nb/nb');

function get_list($fids = array(), $start = 0, $num = 50) {
	global $_G;
	
    if(empty($fids)) {
        return array();
    }
    
    $getCount = C::t('forum_thread')->count_by_fid_sortid_displayorder($fids,4);
    $query = C::t('forum_thread')->fetch_by_fid_sortid($fids,4," dateline DESC ", $start,$num);
    $n = 0;
    
    $list = array();
	foreach($query as $thread) {
        $list[] = $thread;
	}
	return array('count' => $getCount, 'threadlist' => $list);
}

