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
$view = $_GET['view'];
$sortid = isset($_GET['sortid']) ? $_GET['sortid'] : 0;
$gid = isset($_GET['gid']) ? $_GET['gid'] : 0;

loadcache('forum_guide');
if(!in_array($view, array('all','zxqz','tsjb','jyxc', 'hot', 'digest', 'new', 'my', 'newthread', 'sofa'))) {
	$view = 'all';
}
$lang = lang('forum/template');
//$navtitle = $lang['guide'].'-'.$lang['guide_'.$view];
$navtitle = '阿拉帮侬忙 - '.$lang['guide_'.$view];
$perpage = 50;
$start = $perpage * ($_G['page'] - 1);
$data = array();

$announcement = array();

if($_G['page'] == 1) {
    $announcement = C::t('forum_announcement')->fetch_by_displayorder(TIMESTAMP);
    if($announcement){
        $announcement['starttime'] = dgmdate($announcement['starttime'], 'u');
    }
}

if($_GET['rss'] == 1) {
	if($view == 'index' || $view == 'my') {
		showmessage('URL_ERROR');
	}
	$ttl = 30;
	$charset = $_G['config']['output']['charset'];
	dheader("Content-type: application/xml");
	echo 	"<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".
		"<rss version=\"2.0\">\n".
		"  <channel>\n".
		"    <title>{$_G[setting][bbname]} - $lang[guide] - ".$lang['guide_'.$view]."</title>\n".
		"    <link>{$_G[siteurl]}forum.php?mod=guide&amp;view=$view</link>\n".
		"    <description>".$lang['guide_'.$view]."</description>\n".
		"    <copyright>Copyright(C) {$_G[setting][bbname]}</copyright>\n".
		"    <generator>Discuz! Board by Comsenz Inc.</generator>\n".
		"    <lastBuildDate>".gmdate('r', TIMESTAMP)."</lastBuildDate>\n".
		"    <ttl>$ttl</ttl>\n".
		"    <image>\n".
		"      <url>{$_G[siteurl]}static/image/common/logo_88_31.gif</url>\n".
		"      <title>{$_G[setting][bbname]}</title>\n".
		"      <link>{$_G[siteurl]}</link>\n".
		"    </image>\n";

	$info = C::t('forum_rsscache')->fetch_all_by_guidetype($view, $perpage);
	if(empty($info) || (TIMESTAMP - $info[0]['lastupdate'] > $ttl * 60)) {
		update_guide_rsscache($view, $perpage);
	}
	foreach($info as $thread) {
		list($thread['description'], $attachremote, $attachfile, $attachsize) = explode("\t", $thread['description']);
		if($attachfile) {
			if($attachremote) {
				$filename = $_G['setting']['ftp']['attachurl'].'forum/'.$attachfile;
			} else {
				$filename = $_G['siteurl'].$_G['setting']['attachurl'].'forum/'.$attachfile;
			}
		}
		echo 	"    <item>\n".
			"      <title>".$thread['subject']."</title>\n".
			"      <link>$_G[siteurl]".($trewriteflag ? rewriteoutput('forum_viewthread', 1, '', $thread['tid']) : "forum.php?mod=viewthread&amp;tid=$thread[tid]")."</link>\n".
			"      <description><![CDATA[".dhtmlspecialchars($thread['description'])."]]></description>\n".
			"      <category>".dhtmlspecialchars($thread['forum'])."</category>\n".
			"      <author>".dhtmlspecialchars($thread['author'])."</author>\n".
			($attachfile ? '<enclosure url="'.$filename.'" length="'.$attachsize.'" type="image/jpeg" />' : '').
			"      <pubDate>".gmdate('r', $thread['dateline'])."</pubDate>\n".
			"    </item>\n";
	}
	echo 	"  </channel>\n".
		"</rss>";
	exit();
}
if($view != 'index') {
	$theurl = 'forum.php?mod=guide&view='.$view;
	if($view == 'my') {
		if(!$_G['uid']) {
			showmessage('to_login', '', array(), array('login' => 1));
		}
		$lang = lang('forum/template');
		$filter_array = array( 'common' => $lang['have_posted'], 'save' => $lang['guide_draft'], 'close' => $lang['close'], 'aduit' => $lang['pending'], 'ignored' => $lang['have_ignored'], 'recyclebin' => $lang['forum_recyclebin']);
		$viewtype = in_array($_GET['type'], array('reply', 'thread', 'postcomment')) ? $_GET['type'] : 'thread';
		if($searchkey = stripsearchkey($_GET['searchkey'])) {
			$searchkey = dhtmlspecialchars($searchkey);
		}
		$theurl .= '&type='.$viewtype;
		$filter = in_array($_GET['filter'], array_keys($filter_array)) ? $_GET['filter'] : '';
		$searchbody = 0;
		if($filter) {
			$theurl .= '&filter='.$filter;
			$searchbody = 1;
		}
		if($_GET['fid']) {
			$theurl .= '&fid='.intval($_GET['fid']);
			$searchbody = 1;
		}
		if($searchkey) {
			$theurl .= '&searchkey='.$searchkey;
			$searchbody = 1;
		}
		require_once libfile('function/forumlist');
		$forumlist = forumselect(FALSE, 0, intval($_GET['fid']));
		$data['my'] = get_my_threads($viewtype, $_GET['fid'], $filter, $searchkey, $start, $perpage, $theurl);
		$tids = $data['my']['tids'];
		$posts = $data['my']['posts'];
	} else {
		$data[$view] = get_guide_list($view, $start, $perpage);
	}
    
    
    /*
	if(empty($data['my']['multi'])) {
		$multipage = multi($data[$view]['threadcount'], $perpage, $_G['page'], $theurl, $_G['setting']['threadmaxpages']);
	} else {
		$multipage = $data['my']['multi'];
	}
    */
} else {
	$data['hot'] = get_guide_list('hot', 0, 30);
	$data['digest'] = get_guide_list('digest', 0, 30);
	$data['new'] = get_guide_list('new', 0, 30);
	$data['newthread'] = get_guide_list('newthread', 0, 30);
}
$multipage = multi($data[$view]['totalcount'],$perpage,$_G['page'], $theurl,$_G['setting']['threadmaxpages']);
    
loadcache('stamps');
$currentview[$view] = 'class="xw1 a"';
$_G['forum_list'] = get_forums(array(1));
$_G['forum_topnav'] = 1;

$data = thread_add_icon($data,'dbdateline',true);
$forumlist = forumselect(FALSE, 0, intval($_GET['fid']));



/**
 * 获取全局置顶 
 */
$gst = C::t('forum_thread')->fetch_all_by_displayorder(3,'>=',0 , 10);
$globalStickTids = array();
$globlStickList = array();
// 全局置顶帖子
foreach($gst as $v){
    $globalStickTids[] = $v['tid'];
    $v['dbdateline'] = $v['dateline'];
    $v['dateline'] = dgmdate($v['dateline'],'u');
    $v['lastpost'] = dgmdate($v['lastpost'],'u');
    $globlStickList[$v['tid']] = $v;
}

if($globalStickTids){
    $globlStickInfo = C::t('forum_threadmod')->fetch_all_by_tid_status($globalStickTids, array('STK','EST'), array('status' =>1));
    foreach($globlStickInfo as $v){
        if($v['action'] == 'EST' && $v['expiration'] >= TIMESTAMP){
            unset($globlStickList[$v['tid']]);
        }
    }
}

$globlStickList = thread_add_icon_by_row($globlStickList,'dbdateline',true);
$navigation = $view != 'index' ? ' <em>&rsaquo;</em> <a href="forum.php?mod=guide&view='.$view.'">'.$lang['guide_'.$view].'</a>' : '';

if($_G['debugtpl'] && !defined('IN_MOBILE')){
	$navigation = str_replace('&rsaquo;','',$navigation);
}

//获取用户消息数
$newpmcount = $announcepm  = 0;
if ($_G['uid']) {
    loaducenter();
    foreach(C::t('common_member_grouppm')->fetch_all_by_uid($_G['uid'], 1) as $gpmid => $gpuser) {
        $gpmstatus[$gpmid] = $gpuser['status'];
        if($gpuser['status'] == 0) {
            $announcepm ++;
        }
    }
    $newpmarr = uc_pm_checknew($_G['uid'], 1);
    $newpm = $newpmarr['newpm'];
    $newpmcount = $newpm + $announcepm;
}

$currentTopNavTitle = $_G['setting']['navs'][2]['navname'];
        
include template('forum/guide');

function get_guide_list($view, $start = 0, $num = 50, $again = 0) {
	global $_G ,$lang ;
	$setting_guide = unserialize($_G['setting']['guide']);
	if(!in_array($view, array('all', 'zxqz','tsjb','jyxc', 'hot', 'digest', 'new', 'newthread', 'sofa'))) {
		return array();
	}
	loadcache('forums');
	$cachetimelimit = ($view != 'sofa') ? 900 : 60;
    if($view != 'all'){
        $cache = $_G['cache']['forum_guide'][$view.($view=='sofa' && $_G['fid'] ? $_G['fid'] : '')];
    }
	if($cache && (TIMESTAMP - $cache['cachetime']) < $cachetimelimit) {
		$tids = $cache['data'];
		$threadcount = count($tids);
		$tids = array_slice($tids, $start, $num, true);
		$updatecache = false;
		if(empty($tids)) {
			return array();
		}
	} else {
		$dateline = 0;
		$maxnum = 50000;
		if($setting_guide[$view.'dt']) {
			$dateline = time() - intval($setting_guide[$view.'dt']);
		}

		if($view != 'sofa') {
			$maxtid = C::t('forum_thread')->fetch_max_tid();
			$limittid = max(0,($maxtid - $maxnum));
			if($again) {
				$limittid = max(0,($limittid - $maxnum));
			}
			$tids = array();
		}
        
        if(!empty($_GET['gid'])){
            // 如果是 父级板块
            $tempForums = C::t('forum_forum')->fetch_all_fids(0,'',$_GET['gid']);
            foreach($tempForums as $forum) {
                //获取可用板块
                if($forum['type'] != 'group' && $forum['status'] > 0 && $forum['isdepartment'] && !$forum['viewperm'] && !$forum['havepassword']) {
                    $fids[] = $forum['fid'];
                }
            }
        }else{
            
            $usedForums = C::t('forum_forum')->fetch_all_fids();
            foreach($usedForums as $forum) {
                //获取可用板块
                if($forum['type'] != 'group' && $forum['status'] > 0 && $forum['isdepartment'] && !$forum['viewperm'] && !$forum['havepassword']) {
                    $fids[] = $forum['fid'];
                }
            }
        }
		
		if(empty($fids)) {
			return array();
		}
        
        $typeList = array();
        $types = array();
        if($fids){
            $typeList = C::t('forum_threadclass')->fetch_all_by_fid($fids);
            if(in_array($view,array('zxqz','tsjb','jyxc'))){
                foreach($typeList as $v){
                    if($v['name'] == $lang['guide_'.$view]){
                        $types[] = $v['typeid'];
                    }
                }
            }
        }
        
        
		if($view == 'sofa') {
			if($_GET['fid']) {
				$sofa = C::t('forum_sofa')->fetch_all_by_fid($_GET['fid'], $start, $num);
			} else {
				$sofa = C::t('forum_sofa')->range($start, $num);
				foreach($sofa as $sofatid => $sofathread) {
					if(!in_array($sofathread, $fids)) {
						unset($sofathread[$sofatid]);
					}
				}
			}
			$tids = array_keys($sofa);
		}
		$updatecache = true;
	}
    
    $query = C::t('forum_thread')->fetch_all_for_guide($view, $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline, $start, $num , $fids,$types);
    $count = C::t('forum_thread')->count_all_for_guide($view, $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline, $fids,$types);
    $n = 0;
	foreach($query as $thread) {
		//if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
		if(empty($tids) && ($thread['isgroup'])) {
			continue;
		}
		if($thread['displayorder'] < 0) {
			continue;
		}
		$thread = guide_procthread($thread);
		$threadids[] = $thread['tid'];
		/*if($tids || ($n >= $start && $n < ($start + $num))) {*/
			$list[$thread[tid]] = $thread;
			$fids[$thread[fid]] = $thread['fid'];
		/*}*/
		$n ++;
	}
	if($limittid > $maxnum && !$again && count($list) < 50) {
		return get_guide_list($view, $start, $num, 1);
	}
	$forumnames = array();
	if($fids) {
		$forumnames = C::t('forum_forum')->fetch_all_name_by_fid($fids);
	}
	$threadlist = array();
	if($tids) {
		$threadids = array();
		foreach($tids as $key => $tid) {
			if($list[$tid]) {
				$threadlist[$key] = $list[$tid];
				$threadids[] = $tid;
			}
		}
	} else {
		$threadlist = $list;
	}
	unset($list);
	if($updatecache) {
		$threadcount = count($threadids);
		$data = array('cachetime' => TIMESTAMP, 'data' => $threadids);
		$_G['cache']['forum_guide'][$view.($view=='sofa' && $_G['fid'] ? $_G['fid'] : '')] = $data;
		savecache('forum_guide', $_G['cache']['forum_guide']);
	}
	return array('forumnames' => $forumnames, 'threadcount' => $threadcount, 'threadlist' => $threadlist,'totalcount' => $count[0]['num']);
}

function get_my_threads($viewtype, $fid = 0, $filter = '', $searchkey = '', $start = 0, $perpage = 20, $theurl = '') {
	global $_G;
	$fid = $fid ? intval($fid) : null;
	loadcache('forums');
	$dglue = '=';
	if($viewtype == 'thread') {
		$authorid = $_G['uid'];
		$dglue = '=';
		if($filter == 'recyclebin') {
			$displayorder = -1;
		} elseif($filter == 'aduit') {
			$displayorder = -2;
		} elseif($filter == 'ignored') {
			$displayorder = -3;
		} elseif($filter == 'save') {
			$displayorder = -4;
		} elseif($filter == 'close') {
			$closed = 1;
		} elseif($filter == 'common') {
			$closed = 0;
			$displayorder = 0;
			$dglue = '>=';
		}

		$gids = $fids = $forums = array();
		foreach(C::t('forum_thread')->fetch_all_by_authorid_displayorder($authorid, $displayorder, $dglue, $closed, $searchkey, $start, $perpage, null, $fid) as $tid => $value) {
			if(!isset($_G['cache']['forums'][$value['fid']])) {
				$gids[$value['fid']] = $value['fid'];
			} else {
				$forumnames[$value['fid']] = array('fid'=> $value['fid'], 'name' => $_G['cache']['forums'][$value['fid']]['name']);
			}
			$list[$value['tid']] = guide_procthread($value);
		}

		if(!empty($gids)) {
			$gforumnames = C::t('forum_forum')->fetch_all_name_by_fid($gids);
			foreach($gforumnames as $fid => $val) {
				$forumnames[$fid] = $val;
			}
		}
		$listcount = count($list);
	} elseif($viewtype == 'postcomment') {
		require_once libfile('function/post');
		$pids = $tids = array();
		$postcommentarr = C::t('forum_postcomment')->fetch_all_by_authorid($_G['uid'], $start, $perpage);
		foreach($postcommentarr as $value) {
			$pids[] = $value['pid'];
			$tids[] = $value['tid'];
		}
		$pids = C::t('forum_post')->fetch_all(0, $pids);
		$tids = C::t('forum_thread')->fetch_all($tids);

		$list = $fids = array();
		foreach($postcommentarr as $value) {
			$value['authorid'] = $pids[$value['pid']]['authorid'];
			$value['fid'] = $pids[$value['pid']]['fid'];
			$value['invisible'] = $pids[$value['pid']]['invisible'];
			$value['dateline'] = $pids[$value['pid']]['dateline'];
			$value['message'] = $pids[$value['pid']]['message'];
			$value['special'] = $tids[$value['tid']]['special'];
			$value['status'] = $tids[$value['tid']]['status'];
			$value['subject'] = $tids[$value['tid']]['subject'];
			$value['digest'] = $tids[$value['tid']]['digest'];
			$value['attachment'] = $tids[$value['tid']]['attachment'];
			$value['replies'] = $tids[$value['tid']]['replies'];
			$value['views'] = $tids[$value['tid']]['views'];
			$value['lastposter'] = $tids[$value['tid']]['lastposter'];
			$value['lastpost'] = $tids[$value['tid']]['lastpost'];
			$value['icon'] = $tids[$value['tid']]['icon'];
			$value['tid'] = $pids[$value['pid']]['tid'];

			$fids[] = $value['fid'];
			$value['comment'] = messagecutstr($value['comment'], 100);
			$list[] = guide_procthread($value);
		}
		unset($pids, $tids, $postcommentarr);
		if($fids) {
			$fids = array_unique($fids);
			$forumnames = C::t('forum_forum')->fetch_all_name_by_fid($gids);
		}
		$listcount = count($list);
	} else {
		$invisible = null;

		if($filter == 'recyclebin') {
			$invisible = -5;
		} elseif($filter == 'aduit') {
			$invisible = -2;
		} elseif($filter == 'save' || $filter == 'ignored') {
			$invisible = -3;
			$displayorder = -4;
		} elseif($filter == 'close') {
			$closed = 1;
		} elseif($filter == 'common') {
			$invisible = 0;
			$displayorder = 0;
			$dglue = '>=';
			$closed = 0;
		}
		require_once libfile('function/post');
		$posts = C::t('forum_post')->fetch_all_by_authorid(0, $_G['uid'], true, 'DESC', $start, $perpage, null, $invisible, $fid, $followfid);
		$listcount = count($posts);
		foreach($posts as $pid => $post) {
			$tids[$post['tid']][] = $pid;
			$post['message'] = !getstatus($post['status'], 2) || $post['authorid'] == $_G['uid'] ? messagecutstr($post['message'], 100) : '';
			$posts[$pid] = $post;
		}
		if(!empty($tids)) {
			$threads = C::t('forum_thread')->fetch_all_by_tid_displayorder(array_keys($tids), $displayorder, $dglue, array(), $closed);
			foreach($threads as $tid => $thread) {
				if(!isset($_G['cache']['forums'][$thread['fid']])) {
					$gids[$thread['fid']] = $thread['fid'];
				} else {
					$forumnames[$thread[fid]] = array('fid' => $thread['fid'], 'name' => $_G['cache']['forums'][$thread[fid]]['name']);
				}
				$threads[$tid] = guide_procthread($thread);
			}
			if(!empty($gids)) {
				$groupforums = C::t('forum_forum')->fetch_all_name_by_fid($gids);
				foreach($groupforums as $fid => $val) {
					$forumnames[$fid] = $val;
				}
			}
			$list = array();
			foreach($tids as $key => $val) {
				$list[$key] = $threads[$key];
			}
			unset($threads);
		}
	}
	$multi = simplepage($listcount, $perpage, $_G['page'], $theurl);
	return array('forumnames' => $forumnames, 'threadcount' => $listcount, 'threadlist' => $list, 'multi' => $multi, 'tids' => $tids, 'posts' => $posts);
}

function guide_procthread($thread) {
	global $_G;
	$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
	$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
	$thread['multipage'] = '';
	$topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
	if($topicposts > $_G['ppp']) {
		$pagelinks = '';
		$thread['pages'] = ceil($topicposts / $_G['ppp']);
		for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
			$pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$i\">$i</a>";
		}
		if($thread['pages'] > 6) {
			$pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$thread[pages]\">$thread[pages]</a>";
		}
		$thread['multipage'] = '&nbsp;...'.$pagelinks;
	}

	if($thread['highlight']) {
		$string = sprintf('%02d', $thread['highlight']);
		$stylestr = sprintf('%03b', $string[0]);

		$thread['highlight'] = ' style="';
		$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
		$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
		$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
		$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}

	$thread['recommendicon'] = '';
	if(!empty($_G['setting']['recommendthread']['status']) && $thread['recommends']) {
		foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
			if($thread['recommends'] > $i) {
				$thread['recommendicon'] = $k+1;
				break;
			}
		}
	}

	$thread['moved'] = $thread['heatlevel'] = $thread['new'] = 0;
	$thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
	$thread['folder'] = 'common';
	$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
	if($thread['replies'] > $thread['views']) {
		$thread['views'] = $thread['replies'];
	}
	if($_G['setting']['heatthread']['iconlevels']) {
		foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
			if($thread['heats'] > $i) {
				$thread['heatlevel'] = $k + 1;
				break;
			}
		}
	}
	$thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
	$thread['dbdateline'] = $thread['dateline'];
	$thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
	$thread['dblastpost'] = $thread['lastpost'];
	$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');

	if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
		$thread['id'] = 'stickthread_'.$thread['tid'];
	} else {
		$thread['id'] = 'normalthread_'.$thread['tid'];
	}
	$thread['rushreply'] = getstatus($thread['status'], 3);
	return $thread;
}

function update_guide_rsscache($type, $perpage) {
	global $_G;
	$processname = 'guide_rss_cache';
	if(discuz_process::islocked($processname, 600)) {
		return false;
	}
	C::t('forum_rsscache')->delete_by_guidetype($type);
	require_once libfile('function/post');
	$data = get_guide_list($type, 0, $perpage);
	foreach($data['threadlist'] as $thread) {
		$thread['author'] = $thread['author'] != '' ? addslashes($thread['author']) : 'Anonymous';
		$thread['subject'] = addslashes($thread['subject']);
		$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);
		$attachdata = '';
		if($post['attachment'] == 2) {
			$attach = C::t('forum_attachment_n')->fetch_max_image('tid:'.$thread['tid'], 'pid', $post['pid']);
			$attachdata = "\t".$attach['remote']."\t".$attach['attachment']."\t".$attach['filesize'];
		}
		$thread['message'] = $post['message'];
		$thread['status'] = $post['status'];
		$thread['description'] = $thread['readperm'] > 0 || $thread['price'] > 0 || $thread['status'] & 1 ? '' : addslashes(messagecutstr($thread['message'], 250 - strlen($attachdata)).$attachdata);
		C::t('forum_rsscache')->insert(array(
			'lastupdate'=>$_G['timestamp'],
			'fid'=>$thread['fid'],
			'tid'=>$thread['tid'],
			'dateline'=>$thread['dbdateline'],
			'forum'=>strip_tags($data['forumnames'][$thread[fid]]['name']),
			'author'=>$thread['author'],
			'subject'=>$thread['subject'],
			'description'=>$thread['description'],
			'guidetype'=>$type
		), false, true);
	}
	discuz_process::unlock($processname);
	return true;
}
?>