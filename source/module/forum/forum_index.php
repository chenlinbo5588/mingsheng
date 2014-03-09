<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_index.php 33353 2013-05-31 03:05:17Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$gid = intval(getgpc('gid'));
$showoldetails = get_index_online_details();
$list = array();

if(!$_G['uid'] && !$gid && $_G['setting']['cacheindexlife'] && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
	get_index_page_guest_cache();
}

$newthreads = round((TIMESTAMP - $_G['member']['lastvisit'] + 600) / 1000) * 1000;

$catlist = $forumlist = $sublist = $forumname = $collapse = $favforumlist = array();
$threads = $posts = $todayposts = $announcepm = 0;
$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
$postdata[0] = intval($postdata[0]);
$postdata[1] = intval($postdata[1]);

list($navtitle, $metadescription, $metakeywords) = get_seosetting('forum');
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][2]['navname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metadescription) {
	$metadescription = $navtitle;
}
if(!$metakeywords) {
	$metakeywords = $navtitle;
}

if($_G['setting']['indexhot']['status'] && $_G['cache']['heats']['expiration'] < TIMESTAMP) {
	require_once libfile('function/cache');
	updatecache('heats');
}

if($_G['uid'] && empty($_G['cookie']['nofavfid'])) {
	$favfids = array();
	$forum_favlist = C::t('home_favorite')->fetch_all_by_uid_idtype($_G['uid'], 'fid');
	if(!$forum_favlist) {
		dsetcookie('nofavfid', 1, 31536000);
	}
	foreach($forum_favlist as $key => $favorite) {
		if(defined('IN_MOBILE')) {
			$forum_favlist[$key]['title'] = strip_tags($favorite['title']);
		}
		$favfids[] = $favorite['id'];
	}
	if($favfids) {
		$favforumlist = C::t('forum_forum')->fetch_all($favfids);
		$favforumlist_fields = C::t('forum_forumfield')->fetch_all($favfids);
		foreach($favforumlist as $id => $forum) {
			if($favforumlist_fields[$forum['fid']]['fid']) {
				$favforumlist[$id] = array_merge($forum, $favforumlist_fields[$forum['fid']]);
			}
			forum($favforumlist[$id]);
		}

	}
}


if(!$gid && ($_G['setting']['collectionrecommendnum'] || !$_G['setting']['hidefollowcollection'])) {
	require_once libfile('function/cache');
	loadcache('collection_index');
	$collectionrecommend = dunserialize($_G['setting']['collectionrecommend']);
	if(TIMESTAMP - $_G['cache']['collection_index']['dateline'] > 300) {
		$collectiondata = $followdata = array();
		if($_G['setting']['collectionrecommendnum']) {
			if($collectionrecommend['ctids']) {
				$collectionrecommend['ctidsKey'] = array_keys($collectionrecommend['ctids']);
				$tmpcollection = C::t('forum_collection')->fetch_all($collectionrecommend['ctidsKey']);
				foreach($collectionrecommend['ctids'] as $ctid=>$setcollection) {
					if($tmpcollection[$ctid]) {
						$collectiondata[$ctid] = $tmpcollection[$ctid];
					}
				}
				unset($tmpcollection, $ctid, $setcollection);
			}
			if($collectionrecommend['autorecommend']) {
				require_once libfile('function/collection');
				$autorecommenddata = getHotCollection(500);
			}
		}

		savecache('collection_index', array('dateline' => TIMESTAMP, 'data' => $collectiondata, 'auto' => $autorecommenddata));
		$collectiondata = array('data' => $collectiondata, 'auto' => $autorecommenddata);
	} else {
		$collectiondata = &$_G['cache']['collection_index'];
	}
	if($_G['setting']['showfollowcollection']) {
		$followcollections = $_G['uid'] ? C::t('forum_collectionfollow')->fetch_all_by_uid($_G['uid']) : array();;
		if($followcollections) {
			$collectiondata['follows'] = C::t('forum_collection')->fetch_all(array_keys($followcollections), 'dateline', 'DESC', 0, $_G['setting']['showfollowcollection']);
		}
	}
	if($collectionrecommend['autorecommend'] && $collectiondata['auto']) {
		$randrecommend = array_rand($collectiondata['auto'], min($collectionrecommend['autorecommend'], count($collectiondata['auto'])));
		if($randrecommend && !is_array($randrecommend)) {
			$collectiondata['data'][$randrecommend] = $collectiondata['auto'][$randrecommend];
		} else {
			foreach($randrecommend as $ctid) {
				$collectiondata['data'][$ctid] = $collectiondata['auto'][$ctid];
			}
		}
	}
	if($collectiondata['data']) {
		$collectiondata['data'] = array_slice($collectiondata['data'], 0, $collectionrecommend['autorecommend'], true);
	}

}
if(empty($gid) && empty($_G['member']['accessmasks']) && empty($showoldetails)) {
	extract(get_index_memory_by_groupid($_G['member']['groupid']));
	if(defined('FORUM_INDEX_PAGE_MEMORY') && FORUM_INDEX_PAGE_MEMORY) {
		categorycollapse();
		if(!defined('IN_ARCHIVER')) {
			include template('diy:forum/discuz');
		} else {
			include loadarchiver('forum/discuz');
		}
		dexit();
	}
}

$grids = array();
if($_G['setting']['grid']['showgrid']) {
	loadcache('grids');
	$cachelife = $_G['setting']['grid']['cachelife'] ? $_G['setting']['grid']['cachelife'] : 600;
	$now = dgmdate(TIMESTAMP, lang('form/misc', 'y_m_d')).' '.lang('forum/misc', 'week_'.dgmdate(TIMESTAMP, 'w'));
	if(TIMESTAMP - $_G['cache']['grids']['cachetime'] < $cachelife) {
		$grids = $_G['cache']['grids'];
	} else {
		$images = array();
		$_G['setting']['grid']['fids'] = in_array(0, $_G['setting']['grid']['fids']) ? 0 : $_G['setting']['grid']['fids'];

		if($_G['setting']['grid']['gridtype']) {
			$grids['digest'] = C::t('forum_thread')->fetch_all_for_guide('digest', 0, array(), 3, 0, 0, 10, $_G['setting']['grid']['fids']);
		} else {
			$images = C::t('forum_threadimage')->fetch_all_order_by_tid(10);
			foreach($images as $key => $value) {
				$tids[$value['tid']] = $value['tid'];
			}
			$grids['image'] = C::t('forum_thread')->fetch_all_by_tid($tids);
		}
		$grids['newthread'] = C::t('forum_thread')->fetch_all_for_guide('newthread', 0, array(), 0, 0, 0, 10, $_G['setting']['grid']['fids']);

		$grids['newreply'] = C::t('forum_thread')->fetch_all_for_guide('reply', 0, array(), 0, 0, 0, 10, $_G['setting']['grid']['fids']);
		$grids['hot'] = C::t('forum_thread')->fetch_all_for_guide('hot', 0, array(), 3, 0, 0, 10, $_G['setting']['grid']['fids']);

		$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
		foreach($grids as $type => $gridthreads) {
			foreach($gridthreads as $key => $gridthread) {
				$gridthread['dateline'] = str_replace('"', '\'', dgmdate($gridthread['dateline'], 'u', '9999', getglobal('setting/dateformat')));
				$gridthread['lastpost'] = str_replace('"', '\'', dgmdate($gridthread['lastpost'], 'u', '9999', getglobal('setting/dateformat')));
				if($gridthread['highlight'] && $_G['setting']['grid']['highlight']) {
					$string = sprintf('%02d', $gridthread['highlight']);
					$stylestr = sprintf('%03b', $string[0]);

					$gridthread['highlight'] = ' style="';
					$gridthread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
					$gridthread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
					$gridthread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
					$gridthread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
					$gridthread['highlight'] .= '"';
				} else {
					$gridthread['highlight'] = '';
				}
				if($_G['setting']['grid']['textleng']) {
					$gridthread['oldsubject'] = dhtmlspecialchars($gridthread['subject']);
					$gridthread['subject'] = cutstr($gridthread['subject'], $_G['setting']['grid']['textleng']);
				}

				$grids[$type][$key] = $gridthread;
			}
		}
		if(!$_G['setting']['grid']['gridtype']) {

			$focuspic = $focusurl = $focustext = array();
			$grids['focus'] = 'config=5|0xffffff|0x0099ff|50|0xffffff|0x0099ff|0x000000';
			foreach($grids['image'] as $ithread) {
				if($ithread['displayorder'] < 0) {
					continue;
				}
				if($images[$ithread['tid']]['remote']) {
					$imageurl = $_G['setting']['ftp']['attachurl'].'forum/'.$images[$ithread['tid']]['attachment'];
				} else {
					$imageurl = $_G['setting']['attachurl'].'forum/'.$images[$ithread['tid']]['attachment'];
				}
				$grids['slide'][$ithread['tid']] = array(
						'image' => $imageurl,
						'url' => 'forum.php?mod=viewthread&tid='.$ithread['tid'],
						'subject' => $ithread['subject']
					);
			}
		}
		$grids['cachetime'] = TIMESTAMP;
		savecache('grids', $grids);
	}
}

if(!$gid && (!defined('FORUM_INDEX_PAGE_MEMORY') || !FORUM_INDEX_PAGE_MEMORY)) {
	$announcements = get_index_announcements();


	$forums = C::t('forum_forum')->fetch_all_by_status(1);
	$fids = array();
	foreach($forums as $forum) {
		$fids[$forum['fid']] = $forum['fid'];
	}

	$forum_access = array();
	if(!empty($_G['member']['accessmasks'])) {
		$forum_access = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
	}

	$forum_fields = C::t('forum_forumfield')->fetch_all($fids);

	foreach($forums as $forum) {
		if($forum_fields[$forum['fid']]['fid']) {
			$forum = array_merge($forum, $forum_fields[$forum['fid']]);
		}
		if($forum_access['fid']) {
			$forum = array_merge($forum, $forum_access[$forum['fid']]);
		}
		$forumname[$forum['fid']] = strip_tags($forum['name']);
		$forum['extra'] = empty($forum['extra']) ? array() : dunserialize($forum['extra']);
		if(!is_array($forum['extra'])) {
			$forum['extra'] = array();
		}

		if($forum['type'] != 'group') {

			$threads += $forum['threads'];
			$posts += $forum['posts'];
			$todayposts += $forum['todayposts'];

			if($forum['type'] == 'forum' && isset($catlist[$forum['fup']])) {
				if(forum($forum)) {
					$catlist[$forum['fup']]['forums'][] = $forum['fid'];
					$forum['orderid'] = $catlist[$forum['fup']]['forumscount']++;
					$forum['subforums'] = '';
					$forumlist[$forum['fid']] = $forum;
				}

			} elseif(isset($forumlist[$forum['fup']])) {

				$forumlist[$forum['fup']]['threads'] += $forum['threads'];
				$forumlist[$forum['fup']]['posts'] += $forum['posts'];
				$forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];
				if($_G['setting']['subforumsindex'] && $forumlist[$forum['fup']]['permission'] == 2 && !($forumlist[$forum['fup']]['simple'] & 16) || ($forumlist[$forum['fup']]['simple'] & 8)) {
					$forumurl = !empty($forum['domain']) && !empty($_G['setting']['domain']['root']['forum']) ? 'http://'.$forum['domain'].'.'.$_G['setting']['domain']['root']['forum'] : 'forum.php?mod=forumdisplay&fid='.$forum['fid'];
					$forumlist[$forum['fup']]['subforums'] .= (empty($forumlist[$forum['fup']]['subforums']) ? '' : ', ').'<a href="'.$forumurl.'" '.(!empty($forum['extra']['namecolor']) ? ' style="color: ' . $forum['extra']['namecolor'].';"' : '') . '>'.$forum['name'].'</a>';
				}
			}

		} else {

			if($forum['moderators']) {
			 	$forum['moderators'] = moddisplay($forum['moderators'], 'flat');
			}
			$forum['forumscount'] 	= 0;
			$catlist[$forum['fid']] = $forum;
            
		}
	}
	unset($forum_access, $forum_fields);

	foreach($catlist as $catid => $category) {
		$catlist[$catid]['collapseimg'] = 'collapsed_no.gif';
		if($catlist[$catid]['forumscount'] && $category['forumcolumns']) {
			$catlist[$catid]['forumcolwidth'] = (floor(100 / $category['forumcolumns']) - 0.1).'%';
			$catlist[$catid]['endrows'] = '';
			if($colspan = $category['forumscount'] % $category['forumcolumns']) {
				while(($category['forumcolumns'] - $colspan) > 0) {
					$catlist[$catid]['endrows'] .= '<td width="'.$catlist[$catid]['forumcolwidth'].'">&nbsp;</td>';
					$colspan ++;
				}
				$catlist[$catid]['endrows'] .= '</tr>';
			}
		} elseif(empty($category['forumscount'])) {
			unset($catlist[$catid]);
		}
	}
	unset($catid, $category);

	if(isset($catlist[0]) && $catlist[0]['forumscount']) {
		$catlist[0]['fid'] = 0;
		$catlist[0]['type'] = 'group';
		$catlist[0]['name'] = $_G['setting']['bbname'];
		$catlist[0]['collapseimg'] = 'collapsed_no.gif';
	} else {
		unset($catlist[0]);
	}

	if(!IS_ROBOT && ($_G['setting']['whosonlinestatus'] == 1 || $_G['setting']['whosonlinestatus'] == 3)) {
		$_G['setting']['whosonlinestatus'] = 1;

		$onlineinfo = explode("\t", $_G['cache']['onlinerecord']);
		if(empty($_G['cookie']['onlineusernum'])) {
			$onlinenum = C::app()->session->count();
			if($onlinenum > $onlineinfo[0]) {
				$onlinerecord = "$onlinenum\t".TIMESTAMP;
				C::t('common_setting')->update('onlinerecord', $onlinerecord);
				savecache('onlinerecord', $onlinerecord);
				$onlineinfo = array($onlinenum, TIMESTAMP);
			}
			dsetcookie('onlineusernum', intval($onlinenum), 300);
		} else {
			$onlinenum = intval($_G['cookie']['onlineusernum']);
		}
		$onlineinfo[1] = dgmdate($onlineinfo[1], 'd');

		$detailstatus = $showoldetails == 'yes' || (((!isset($_G['cookie']['onlineindex']) && !$_G['setting']['whosonline_contract']) || $_G['cookie']['onlineindex']) && $onlinenum < 500 && !$showoldetails);

		$guestcount = $membercount = 0;
		if(!empty($_G['setting']['sessionclose'])) {
			$detailstatus = false;
			$membercount = C::app()->session->count(1);
			$guestcount = $onlinenum - $membercount;
		}

		if($detailstatus) {
			$actioncode = lang('action');

			$_G['uid'] && updatesession();
			$whosonline = array();

			$_G['setting']['maxonlinelist'] = $_G['setting']['maxonlinelist'] ? $_G['setting']['maxonlinelist'] : 500;
			foreach(C::app()->session->fetch_member(1, 0, $_G['setting']['maxonlinelist']) as $online){
				$membercount ++;
				if($online['invisible']) {
					$invisiblecount++;
					continue;
				} else {
					$online['icon'] = !empty($_G['cache']['onlinelist'][$online['groupid']]) ? $_G['cache']['onlinelist'][$online['groupid']] : $_G['cache']['onlinelist'][0];
				}
				$online['lastactivity'] = dgmdate($online['lastactivity'], 't');
				$whosonline[] = $online;
			}
			if(isset($_G['cache']['onlinelist'][7]) && $_G['setting']['maxonlinelist'] > $membercount) {
				foreach(C::app()->session->fetch_member(2, 0, $_G['setting']['maxonlinelist'] - $membercount) as $online){
					$online['icon'] = $_G['cache']['onlinelist'][7];
					$online['username'] = $_G['cache']['onlinelist']['guest'];
					$online['lastactivity'] = dgmdate($online['lastactivity'], 't');
					$whosonline[] = $online;
				}
			}
			unset($actioncode, $online);

			if($onlinenum > $_G['setting']['maxonlinelist']) {
				$membercount = C::app()->session->count(1);
				$invisiblecount = C::app()->session->count_invisible();
			}

			if($onlinenum < $membercount) {
				$onlinenum = C::app()->session->count();
				dsetcookie('onlineusernum', intval($onlinenum), 300);
			}

			$invisiblecount = intval($invisiblecount);
			$guestcount = $onlinenum - $membercount;

			unset($online);
		}

	} else {
		$_G['setting']['whosonlinestatus'] = 0;
	}

	if(defined('FORUM_INDEX_PAGE_MEMORY') && !FORUM_INDEX_PAGE_MEMORY) {
		$key = !IS_ROBOT ? $_G['member']['groupid'] : 'for_robot';
		memory('set', 'forum_index_page_'.$key, array(
			'catlist' => $catlist,
			'forumlist' => $forumlist,
			'sublist' => $sublist,
			'whosonline' => $whosonline,
			'onlinenum' => $onlinenum,
			'membercount' => $membercount,
			'guestcount' => $guestcount,
			'grids' => $grids,
			'announcements' => $announcements,
			'threads' => $threads,
			'posts' => $posts,
			'todayposts' => $todayposts,
			'onlineinfo' => $onlineinfo,
			'announcepm' => $announcepm), getglobal('setting/memory/forumindex'));
	}

} else {
	require_once DISCUZ_ROOT.'./source/include/misc/misc_category.php';
}


if(defined('IN_ARCHIVER')) {
	include loadarchiver('forum/discuz');
	exit();
}
categorycollapse();

if($gid && !empty($catlist)) {
	$_G['category'] = $catlist[$gid];
	$forumseoset = array(
		'seotitle' => $catlist[$gid]['seotitle'],
		'seokeywords' => $catlist[$gid]['keywords'],
		'seodescription' => $catlist[$gid]['seodescription']
	);
	$seodata = array('fgroup' => $catlist[$gid]['name']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('threadlist', $seodata, $forumseoset);
	if(empty($navtitle)) {
		$navtitle = $navtitle_g;
		$nobbname = false;
	} else {
		$nobbname = true;
	}
	$_G['fid'] = $gid;
}

$perpage = 50;
$start = $perpage * ($_G['page'] - 1);
$list['threadcount'] = 0;
$extra = rawurlencode(!IS_ROBOT ? 'page='.$page.($forumdisplayadd['page'] ? '&filter='.$filter.$forumdisplayadd['page'] : '') : 'page=1');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();

$tableid = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? intval($_GET['archiveid']) : 0;
$tabKey = isset($_GET['tabkey']) ? $_GET['tabkey'] : 0;

$filterarr = array();
switch ($tabKey) {
    case '1': 
        $filterarr['starttime'] = date('Y-m-d H:i:s');
        $filterarr['insort'] = 2;
        break;
    case '2':
        $filterarr['lastpostmore'] = time() - 3 * 24 * 3600;
    case '3':
        $filterarr['lastpostmore'] = time() - 5 * 24 * 3600;
        break;
    case '4':
        $filterarr['lastpostless'] = time() - 5 * 24 * 3600;
        break;
    case '5':
        $filterarr['lastpostless'] = time() - 7 * 24 * 3600;
        break;
    default:
    case '0':
        break;
}
$list['threadcount'] = C::t('forum_thread')->count_search($filterarr);

$threadlist = C::t('forum_thread')->fetch_all_search($filterarr);
$list['threadlist'] = format_index_list($threadlist);
include template('diy:forum/discuz:'.$gid);

function get_index_announcements() {
	global $_G;
	$announcements = '';
	if($_G['cache']['announcements']) {
		$readapmids = !empty($_G['cookie']['readapmid']) ? explode('D', $_G['cookie']['readapmid']) : array();
		foreach($_G['cache']['announcements'] as $announcement) {
			if(!$announcement['endtime'] || $announcement['endtime'] > TIMESTAMP && (empty($announcement['groups']) || in_array($_G['member']['groupid'], $announcement['groups']))) {
				if(empty($announcement['type'])) {
					$announcements .= '<li><span><a href="forum.php?mod=announcement&id='.$announcement['id'].'" target="_blank" class="xi2">'.$announcement['subject'].
						'</a></span><em>('.dgmdate($announcement['starttime'], 'd').')</em></li>';
				} elseif($announcement['type'] == 1) {
					$announcements .= '<li><span><a href="'.$announcement['message'].'" target="_blank" class="xi2">'.$announcement['subject'].
						'</a></span><em>('.dgmdate($announcement['starttime'], 'd').')</em></li>';
				}
			}
		}
	}
	return $announcements;
}

function get_index_page_guest_cache() {
	global $_G;
	$indexcache = getcacheinfo(0);
	if(TIMESTAMP - $indexcache['filemtime'] > $_G['setting']['cacheindexlife']) {
		@unlink($indexcache['filename']);
		define('CACHE_FILE', $indexcache['filename']);
	} elseif($indexcache['filename']) {
		@readfile($indexcache['filename']);
		$updatetime = dgmdate($indexcache['filemtime'], 'H:i:s');
		$gzip = $_G['gzipcompress'] ? ', Gzip enabled' : '';
		echo "<script type=\"text/javascript\">
			if($('debuginfo')) {
				$('debuginfo').innerHTML = '. This page is cached  at $updatetime $gzip .';
			}
			</script>";
		exit();
	}
}

function get_index_memory_by_groupid($key) {
	$enable = getglobal('setting/memory/forumindex');
	if($enable !== null && memory('check')) {
		if(IS_ROBOT) {
			$key = 'for_robot';
		}
		$ret = memory('get', 'forum_index_page_'.$key);
		define('FORUM_INDEX_PAGE_MEMORY', $ret ? 1 : 0);
		if($ret) {
			return $ret;
		}
	}
	return array('none' => null);
}

function get_index_online_details() {
	$showoldetails = getgpc('showoldetails');
	switch($showoldetails) {
		case 'no': dsetcookie('onlineindex', ''); break;
		case 'yes': dsetcookie('onlineindex', 1, 86400 * 365); break;
	}
	return $showoldetails;
}

function do_forum_bind_domains() {
	global $_G;
	if($_G['setting']['binddomains'] && $_G['setting']['forumdomains']) {
		$loadforum = isset($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']]) ? max(0, intval($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']])) : 0;
		if($loadforum) {
			dheader('Location: '.$_G['setting']['siteurl'].'/forum.php?mod=forumdisplay&fid='.$loadforum);
		}
	}
}
function categorycollapse() {
	global $_G, $collapse, $catlist;
	if(!$_G['uid']) {
		return;
	}
	foreach($catlist as $fid => $forum) {
		if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], '_category_'.$fid.'_') === FALSE) {
			$catlist[$fid]['collapseimg'] = 'collapsed_no.gif';
			$collapse['category_'.$fid] = '';
		} else {
			$catlist[$fid]['collapseimg'] = 'collapsed_yes.gif';
			$collapse['category_'.$fid] = 'display: none';
		}
	}

	for($i = -2; $i <= 0; $i++) {
		if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], '_category_'.$i.'_') === FALSE) {
			$collapse['collapseimg_'.$i] = 'collapsed_no.gif';
			$collapse['category_'.$i] = '';
		} else {
			$collapse['collapseimg_'.$i] = 'collapsed_yes.gif';
			$collapse['category_'.$i] = 'display: none';
		}
	}
}

function format_index_list($threadlist = array()) {
    foreach($threadlist as $thread) {
        $thread['allreplies'] = $thread['replies'] + $thread['comments'];
        $thread['ordertype'] = getstatus($thread['status'], 4);
        if($_G['forum']['picstyle'] && empty($_G['cookie']['forumdefstyle'])) {
            if($thread['fid'] != $_G['fid'] && empty($thread['cover'])) {
                continue;
            }
            $thread['coverpath'] = getthreadcover($thread['tid'], $thread['cover']);
            $thread['cover'] = abs($thread['cover']);
        }
        $thread['forumstick'] = in_array($thread['tid'], $forumstickytids);
        $thread['related_group'] = 0;
        if($_G['forum']['relatedgroup'] && $thread['fid'] != $_G['fid']) {
            if($thread['closed'] > 1) continue;
            $thread['related_group'] = 1;
            $grouptids[] = $thread['tid'];
        }
        $thread['lastposterenc'] = rawurlencode($thread['lastposter']);
        if($thread['typeid'] && !empty($_G['forum']['threadtypes']['prefix']) && isset($_G['forum']['threadtypes']['types'][$thread['typeid']])) {
            if($_G['forum']['threadtypes']['prefix'] == 1) {
                $thread['typehtml'] = '<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'</a>]</em>';
            } elseif($_G['forum']['threadtypes']['icons'][$thread['typeid']] && $_G['forum']['threadtypes']['prefix'] == 2) {
                $thread['typehtml'] = '<em><a title="'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'" href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.'<img style="vertical-align: middle;padding-right:4px;" src="'.$_G['forum']['threadtypes']['icons'][$thread['typeid']].'" alt="'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'" /></a></em>';
            }
            $thread['typename'] = $_G['forum']['threadtypes']['types'][$thread['typeid']];
        } else {
            $thread['typename'] = $thread['typehtml'] = '';
        }

        $thread['sorthtml'] = $thread['sortid'] && !empty($_G['forum']['threadsorts']['prefix']) && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ?
            '<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=sortid&amp;sortid='.$thread['sortid'].'">'.$_G['forum']['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '';
        $thread['multipage'] = '';
        $topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
        $multipate_archive = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? "archiveid={$_GET['archiveid']}" : '';
        if($topicposts > $_G['ppp']) {
            $pagelinks = '';
            $thread['pages'] = ceil($topicposts / $_G['ppp']);
            $realtid = $_G['forum']['status'] != 3 && $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
            for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
                $pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$i\">$i</a>";
            }
            if($thread['pages'] > 6) {
                $pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$thread[pages]\">$thread[pages]</a>";
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
            $thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]].';' : '';
            if($thread['bgcolor']) {
                $thread['highlight'] .= "background-color: $thread[bgcolor];";
            }
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
        $thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
        $thread['dbdateline'] = $thread['dateline'];
        $thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
        $thread['dblastpost'] = $thread['lastpost'];
        $thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
        $thread['hidden'] = $_G['setting']['threadhidethreshold'] && $thread['hidden'] >= $_G['setting']['threadhidethreshold'] || in_array($thread['tid'], $thide);
        if($thread['hidden']) {
            $_G['hiddenexists']++;
        }

        if(isset($_G['setting']['verify']['enabled']) && $_G['setting']['verify']['enabled']) {
            $verifyuids[$thread['authorid']] = $thread['authorid'];
        }
        $authorids[$thread['authorid']] = $thread['authorid'];
        $thread['mobile'] = base_convert(getstatus($thread['status'], 13).getstatus($thread['status'], 12).getstatus($thread['status'], 11), 2, 10);
        $thread['rushreply'] = getstatus($thread['status'], 3);
        if($thread['rushreply']) {
            $rushtids[$thread['tid']] = $thread['tid'];
        }
        $threadids[$threadindex] = $thread['tid'];
        $_G['forum_threadlist'][$threadindex] = $thread;
        $threadindex++;
    }
    return $threadlist;
}

?>