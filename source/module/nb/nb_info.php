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
require_once libfile('function/discuzcode');
$lang = lang('forum/template');

//print_r($_G['setting']['bbname']);
$navtitle = $_G['setting']['bbname'] ;

$tid = empty($_GET['tid']) ? 0 : (int)$_GET['tid'];


$tid_info =  C::t('forum_post')->fetch_all_by_tid_range_position(0,$tid,1,3);
//print_r($tid_info);


$showName = '';
$member = '';
$detailAsk = '';
$detailAnswer = '';
if($tid_info){
    
    
    $i = 0;
    foreach($tid_info as $k => $post){
        //$forum_allowbbcode = $_G['forum']['allowbbcode'] ? 1 : 0;
        $post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], 1, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload'], $post['dbdateline'], $post['first']);
        if($i == 0){
            $detailAsk = $post;
            $member = C::t('common_member_profile')->fetch_all(array($post['authorid']));
        }else{
            $detailAnswer = $post;
        }
        
        $i++;
    }
}

$showName = $detailAsk['author'];
$showAddress = '';
$showMobile = '';
$showSex = '';
if($member){
    foreach($member as $v){
        if(!empty($v['realname'])){
            $showName = $v['realname'];
        }
        
        $showMobile = $v['mobile'];
        $showAddress = $v['resideprovince'].$v['residecity'].$v['residedist'].$v['residecommunity'];
        
        if($v['gender'] == 1 ){
            $showSex ='先生';
        }elseif($v['gender'] == 2){
            $showSex ='女士';
        }
        break;
    }
}
if($showMobile){
    $maskMobile = $showMobile;
    if(strpos($maskMobile,'+') !== false){
        $maskMobile = substr($maskMobile,0,6).'****'.substr($maskMobile,10);
    }else{
        if(strlen($showMobile) == 11){
            $maskMobile = substr($maskMobile,0,3).'****'.substr($maskMobile,7);
        }else{
            $maskMobile = substr($maskMobile,0,5).'****'.substr($maskMobile,9);
        }
    }
    $showMobile = $maskMobile;
    
}

include template('nb/nb_info');


