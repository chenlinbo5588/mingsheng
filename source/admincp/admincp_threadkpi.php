<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadkpi.php 29335 2012-04-05 02:08:34Z cnteacher $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');
if($operation != 'export' && $operation != 'export_tj') {
	cpheader();
}
$sortAr = array(
    'k0' => '未审核',
    'k2' => '已审核',
    'k3' => '已受理',
    'k4' => '已回复',
);

$gradeAr = array(
    'k0' => '未评价',
    'k1' => '不满意',
    'k2' => '一般',
    'k3' => '很满意',
);

//$operation = $_GET['operation'] ? $_GET['operation'] : 'statistics' ;
$operation = $_GET['operation'] ? $_GET['operation'] : 'set' ;

if($operation == 'set') {
	$nav = 'config';
	//$submenu['statistics'] = 1;
	$submenu['set'] = 1;
} elseif ($operation == 'list') {
	$nav = 'nav_threadkpi_list';
	$submenu['list'] = 1;
} elseif ($operation == 'tj') {
	$nav = 'nav_threadkpi_tj';
	$submenu['tj'] = 1;
} else {
	$nav = '';
}

if($nav != '') {
	if(!submitcheck('threadkpisubmit', 1) || $operation == 'list' || $operation == 'tj') {
		shownav('extended', 'nav_threadkpi', $nav);
		showsubmenu('nav_threadkpi', array(
			array('config', 'threadkpi', $submenu['set']),
            array('nav_threadkpi_tj', 'threadkpi&operation=tj', $submenu['tj']),
			array('nav_threadkpi_list', 'threadkpi&operation=list', $submenu['list']),
            /*
			array('nav_card_type', 'threadkpi&operation=type', $submenu['type']),
			array('nav_card_make', 'threadkpi&operation=make', $submenu['make']),
			array(array('menu' => 'nav_card_log', 'submenu' => array(
				array('nav_card_log_add', 'threadkpi&operation=log&do=add', $_GET['do'] == 'add'),
				array('nav_card_log_del', 'threadkpi&operation=log&do=del', $_GET['do'] == 'del'),
				array('nav_card_log_cron', 'threadkpi&operation=log&do=cron', $_GET['do'] == 'cron')
			)), in_array($_GET['do'], array('add', 'del', 'cron')))
             * 
             */
		));
	}
}
if($operation == 'set') {
	if(!submitcheck('threadkpisubmit')) {
        $setting = C::t('forum_kpisetting')->fetch_by_id(1);
        //loadcache('threadkpi');
        //file_put_contents("debug.txt",print_r($_G['cache']['threadkpi'],true));
		showformheader('threadkpi&operation=set&');
		showtableheader();
		showsetting('threadkpi_config_light_green', 'light_green', $setting['light_green'], 'text');
        showsetting('threadkpi_config_light_yellow', 'light_yellow',$setting['light_yellow'], 'text');
        showsetting('threadkpi_config_light_gray', 'light_gray', $setting['light_gray'], 'text');
        showsetting('threadkpi_config_light_red', 'light_red', $setting['light_red'], 'text');
		showsubmit('threadkpisubmit');
		showtablefooter();
		showformfooter();
	} else {
        
        $d = array();
        
        if(is_numeric($_POST['light_green'])){
            $d['light_green'] = intval($_POST['light_green']);
        }
        if(is_numeric($_POST['light_yellow'])){
            $d['light_yellow'] = intval($_POST['light_yellow']);
        }
        if(is_numeric($_POST['light_gray'])){
            $d['light_gray'] = intval($_POST['light_gray']);
        }
        if(is_numeric($_POST['light_red'])){
            $d['light_red'] = intval($_POST['light_red']);
        }
        
        if($d){
            C::t('forum_kpisetting')->update_by_id(1, $d);
            
            //updatecache('setting');
            writetocache('threadkpi', getcachevars(array('light_setting' => $d)));
            savecache('threadkpi', array('light_setting' => $d));
        }
        
		cpmsg('threadkpi_config_succeed', 'action=threadkpi&operation=set', 'succeed');
	}
} elseif($operation == 'list'){
    /*
	if(submitcheck('cardsubmit')) {
		if(is_array($_POST['delete'])) {
			$delnum = C::t('common_card')->delete($_POST['delete']);
			$card_info = serialize(array('num' => ($delnum ? $delnum : 0)));
			$cardlog = array(
				'uid' => $_G['uid'],
				'cardrule' => '',
				'info' => $card_info,
				'dateline' => $_G['timestamp'],
				'operation' => 3,
				'username' => $_G['member']['username']
			);
			C::t('common_card_log')->insert($cardlog);
		}
	}
     * 
     */
	$sqladd = threadkpisql();
	foreach($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== false && $val) {
			if(in_array($key, array('srch_subject','srch_newthread_by','srch_modthread_by','srch_sorthread_by','srch_replythread_by'))){
				$val = rawurlencode($val);
			}
			$export_url[] = $key.'='.$val;
		}
	}

	$perpage = max(20, empty($_GET['perpage']) ? 20 : intval($_GET['perpage']));
    //$perpage = 1;
	echo '<script type="text/javascript" src="static/js/calendar.js"></script>';

	showtips('threadkpi_list_tips');
	
	showformheader('threadkpi', '', 'cdform', 'get');
	showtableheader();
	showtablerow('', array('width="80"', 'width="100"', 'width=100', 'width="260"'),
		array(
			cplang('threadkpi_list_subject'), '<input type="text" name="srch_subject" class="txt" value="'.$_GET['srch_subject'].'" />',
			cplang('threadkpi_list_score').cplang('between'), '<input type="text" name="srch_score_min" class="txt" value="'.$_GET['srch_score_min'].'" />- &nbsp;<input type="text" name="srch_score_max" class="txt" value="'.$_GET['srch_score_max'].'" />',
		)
	);

	echo "<input type='hidden' name='action' value='threadkpi'><input type='hidden' name='operation' value='list'>";
	
    $extcredits_option = "<option value=''>".cplang('nolimit')."</option>";
    if($_GET['srch_grade'] == '3'){
        $extcredits_option .= "<option value='3' selected>很满意</option>";
    }else{
        $extcredits_option .= "<option value='3'>很满意</option>";
    }
    
    if($_GET['srch_grade'] == '2'){
        $extcredits_option .= "<option value='2' selected>一般</option>";
    }else{
        $extcredits_option .= "<option value='2'>一般</option>";
    }
    
    if($_GET['srch_grade'] == '1'){
        $extcredits_option .= "<option value='1' selected>不满意</option>";
    }else{
        $extcredits_option .= "<option value='1'>不满意</option>";
    }
    
    if($_GET['srch_grade'] == '-'){
        $extcredits_option .= "<option value='-' selected>未评价</option>";
    }else{
        $extcredits_option .= "<option value='-'>未评价</option>";
    }
    
	if($_GET['srch_sortid'] == '2'){
         $status_option .= "<option value='2' selected>已审核</option>";
    }else{
        $status_option .= "<option value='2'>已审核</option>";
    }
    
    if($_GET['srch_sortid'] == '3'){
         $status_option .= "<option value='3' selected>已受理</option>";
    }else{
        $status_option .= "<option value='3'>已受理</option>";
    }
    
    if($_GET['srch_sortid'] == '4'){
         $status_option .= "<option value='4' selected>已回复</option>";
    }else{
        $status_option .= "<option value='4'>已回复</option>";
    }
	
	showtablerow('', array(),
		array(
			cplang('threadkpi_list_grade'), '<select name="srch_grade">'.$extcredits_option.'</select>',
			cplang('threadkpi_list_status'), "<select name='srch_sortid'><option value=''>".cplang('nolimit')."</option>".$status_option."</select>",
		)
	);
	showtablerow('', array(),
		array(
			cplang('threadkpi_list_author'), '<input type="text" name="srch_newthread_by" class="txt" value="'.$_GET['srch_newthread_by'].'" />',
			cplang('threadkpi_list_newthreadtime'), '<input type="text" name="srch_newthread_start" class="txt" value="'.$_GET['srch_newthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_newthread_end" class="txt" value="'.$_GET['srch_newthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_modby'), '<input type="text" name="srch_modthread_by" class="txt" value="'.$_GET['srch_modthread_by'].'" />',
			cplang('threadkpi_list_modthreadtime'), '<input type="text" name="srch_modthread_start" class="txt" value="'.$_GET['srch_modthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_modthread_end" class="txt" value="'.$_GET['srch_modthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_sorby'), '<input type="text" name="srch_sorthread_by" class="txt" value="'.$_GET['srch_sorthread_by'].'" />',
			cplang('threadkpi_list_sorthreadtime'), '<input type="text" name="srch_sorthread_start" class="txt" value="'.$_GET['srch_sorthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_sorthread_end" class="txt" value="'.$_GET['srch_sorthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_rlpby'), '<input type="text" name="srch_replythread_by" class="txt" value="'.$_GET['srch_replythread_by'].'" />',
			cplang('threadkpi_list_rlpthreadtime'), '<input type="text" name="srch_replythread_start" class="txt" value="'.$_GET['srch_replythread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_replythread_end" class="txt" value="'.$_GET['srch_replythread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);

    
    $forumlist = forumselect(FALSE, 0, intval($_GET['srch_fid']));
    
	$perpage_selected[$perpage] = "selected=selected";
	showtablerow('', array(),
		array(
			cplang('forum'), "<select name='srch_fid'><option value=''>".cplang('nolimit')."</option>".$forumlist.'</select>',
			cplang('card_search_perpage'), '<select name="perpage" class="ps" onchange="this.form.submit();" ><option value="20" '.$perpage_selected[20].'>'.cplang('perpage_20').'</option><option value="50" '.$perpage_selected[50].'>'.cplang('perpage_50').'</option><option value="100" '.$perpage_selected[100].'>'.cplang('perpage_100').'</option></select>',
		)
	);

	showtablerow('', array('width="40"', 'width="100"', 'width=50', 'width="260"'),
		array(
			'<input type="submit" name="srchbtn" class="btn" value="'.$lang['search'].'" />',''
		)
	);
	showtablefooter();
	showformfooter();

	showformheader('threadkpi&operation=list&');
	showtableheader('threadkpi_list_title');
	showsubtitle(array('帖子ID', '帖子标题', '版块', '状态', '回复天数','得分','灯色','备注', '受理超时','满意度', '发帖时间','审核时间','受理时间','回复时间'));


	$start_limit = ($page - 1) * $perpage;
	$export_url[] = 'start='.$start_limit;
	foreach ($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== FALSE) {
			$url_add .= '&'.$key.'='.$val;
		}
	}
	$url = ADMINSCRIPT.'?action=threadkpi&operation=list&page='.$page.'&perpage='.$perpage.$url_add;
	$count = $sqladd ? C::t('forum_kpilog')->count_by_where($sqladd) : C::t('forum_kpilog')->count();
	if($count) {
		$multipage = multi($count, $perpage, $page, $url, 0, 3);
		foreach(C::t('forum_kpilog')->fetch_all_by_where($sqladd, $start_limit, $perpage) as $result) {
			$list[] = $result;
		}
		

		foreach($list AS $key => $val) {
			showtablerow('', array( '', '', '', '', '', '', '', '', '', '', ''), array(
				$val['tid'],
				"<a href='forum.php?mod=viewthread&tid={$val['tid']}&extra=' target='_blank'>".cutstr($val['subject'], 30).'</a>',
				"<a href='forum.php?mod=forumdisplay&fid={$val['fid']} target='_blank'>".$_G['cache']['forums'][$val['fid']]['name'].'</a>',
				$sortAr['k'.$val['sortid']],
				$val['day_cnt'],
                $val['score'],
				$val['light'],
                $val['remark'],
				$val['sor_expired'] ? "是" : '否',
                $val['grade'],
				$val['newthread'] ? dgmdate($val['newthread']) : ' -- ',
				$val['modthread'] ? dgmdate($val['modthread']) : ' -- ',
				$val['sorthread'] ? dgmdate($val['sorthread']) : ' -- ',
				$val['replythread'] ? (($val['tid'] == 4) ? dgmdate($val['replythread']) : ' -- ') : ' -- '
			));
		}
		echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
		showsubmit('threadkpisubmit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=threadkpi&operation=export&'.implode('&', $export_url).'" title="'.$lang['threadkpi_list_export_title'].'">'.$lang['threadkpi_list_export'].'</a>', $multipage, false);
	}

	showtablefooter();
	showformfooter();

} elseif($operation == 'tj') {
    
    $sqladd = threadkpisql();
	foreach($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== false && $val) {
			if(in_array($key, array('srch_subject','srch_newthread_by','srch_modthread_by','srch_sorthread_by','srch_replythread_by'))){
				$val = rawurlencode($val);
			}
			$export_url[] = $key.'='.$val;
		}
	}

	//$perpage = max(20, empty($_GET['perpage']) ? 20 : intval($_GET['perpage']));
    //$perpage = 1;
	echo '<script type="text/javascript" src="static/js/calendar.js"></script>';

	showtips('threadkpi_tj_tips');
	
	showformheader('threadkpi', '', 'cdform', 'get');
	showtableheader();
	showtablerow('', array('width="80"', 'width="100"', 'width=100', 'width="260"'),
		array(
			cplang('threadkpi_list_subject'), '<input type="text" name="srch_subject" class="txt" value="'.$_GET['srch_subject'].'" />',
			cplang('threadkpi_list_score').cplang('between'), '<input type="text" name="srch_score_min" class="txt" value="'.$_GET['srch_score_min'].'" />- &nbsp;<input type="text" name="srch_score_max" class="txt" value="'.$_GET['srch_score_max'].'" />',
		)
	);

	echo "<input type='hidden' name='action' value='threadkpi'><input type='hidden' name='operation' value='tj'>";
	
    $extcredits_option = "<option value=''>".cplang('nolimit')."</option>";
    if($_GET['srch_grade'] == '3'){
        $extcredits_option .= "<option value='3' selected>很满意</option>";
    }else{
        $extcredits_option .= "<option value='3'>很满意</option>";
    }
    
    if($_GET['srch_grade'] == '2'){
        $extcredits_option .= "<option value='2' selected>一般</option>";
    }else{
        $extcredits_option .= "<option value='2'>一般</option>";
    }
    
    if($_GET['srch_grade'] == '1'){
        $extcredits_option .= "<option value='1' selected>不满意</option>";
    }else{
        $extcredits_option .= "<option value='1'>不满意</option>";
    }
    
    if($_GET['srch_grade'] == '-'){
        $extcredits_option .= "<option value='-' selected>未评价</option>";
    }else{
        $extcredits_option .= "<option value='-'>未评价</option>";
    }
    
	if($_GET['srch_sortid'] == '2'){
         $status_option .= "<option value='2' selected>已审核</option>";
    }else{
        $status_option .= "<option value='2'>已审核</option>";
    }
    
    if($_GET['srch_sortid'] == '3'){
         $status_option .= "<option value='3' selected>已受理</option>";
    }else{
        $status_option .= "<option value='3'>已受理</option>";
    }
    
    if($_GET['srch_sortid'] == '4'){
         $status_option .= "<option value='4' selected>已回复</option>";
    }else{
        $status_option .= "<option value='4'>已回复</option>";
    }
	
	showtablerow('', array(),
		array(
			cplang('threadkpi_list_grade'), '<select name="srch_grade">'.$extcredits_option.'</select>',
			cplang('threadkpi_list_status'), "<select name='srch_sortid'><option value=''>".cplang('nolimit')."</option>".$status_option."</select>",
		)
	);
	showtablerow('', array(),
		array(
			cplang('threadkpi_list_author'), '<input type="text" name="srch_newthread_by" class="txt" value="'.$_GET['srch_newthread_by'].'" />',
			cplang('threadkpi_list_newthreadtime'), '<input type="text" name="srch_newthread_start" class="txt" value="'.$_GET['srch_newthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_newthread_end" class="txt" value="'.$_GET['srch_newthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_modby'), '<input type="text" name="srch_modthread_by" class="txt" value="'.$_GET['srch_modthread_by'].'" />',
			cplang('threadkpi_list_modthreadtime'), '<input type="text" name="srch_modthread_start" class="txt" value="'.$_GET['srch_modthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_modthread_end" class="txt" value="'.$_GET['srch_modthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_sorby'), '<input type="text" name="srch_sorthread_by" class="txt" value="'.$_GET['srch_sorthread_by'].'" />',
			cplang('threadkpi_list_sorthreadtime'), '<input type="text" name="srch_sorthread_start" class="txt" value="'.$_GET['srch_sorthread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_sorthread_end" class="txt" value="'.$_GET['srch_sorthread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);
    
    showtablerow('', array(),
		array(
			cplang('threadkpi_list_rlpby'), '<input type="text" name="srch_replythread_by" class="txt" value="'.$_GET['srch_replythread_by'].'" />',
			cplang('threadkpi_list_rlpthreadtime'), '<input type="text" name="srch_replythread_start" class="txt" value="'.$_GET['srch_replythread_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_replythread_end" class="txt" value="'.$_GET['srch_replythread_end'].'" onclick="showcalendar(event, this)" />',
		)
	);

    
    $forumlist = forumselect(FALSE, 0, intval($_GET['srch_fid']));
    
	$perpage_selected[$perpage] = "selected=selected";
	showtablerow('', array(),
		array(
			cplang('forum'), "<select name='srch_fid'><option value=''>".cplang('nolimit')."</option>".$forumlist.'</select>',
			cplang('card_search_perpage'), '<select name="perpage" class="ps" onchange="this.form.submit();" ><option value="20" '.$perpage_selected[20].'>'.cplang('perpage_20').'</option><option value="50" '.$perpage_selected[50].'>'.cplang('perpage_50').'</option><option value="100" '.$perpage_selected[100].'>'.cplang('perpage_100').'</option></select>',
		)
	);
	
	
	if($_GET['group_light'] == '是'){
        $light_option .= "<option value='否'>否</option><option value='是' selected>是</option>";
    }else{
        $light_option .= "<option value='否' selected>否</option><option value='是'>是</option>";
    }
    
	
	showtablerow('', array('width="40"', 'width="100"', 'width=50', 'width="260"'),
		array(
			'按灯色统计', "<select name='group_light'>{$light_option}</select>",
			'<input type="submit" name="srchbtn" class="btn" value="'.$lang['search'].'" />',''
		)
	);
	showtablefooter();
	showformfooter();

	showformheader('threadkpi&operation=list&');
	showtableheader('threadkpi_tj_title');
	
	
	if($_GET['group_light'] == "是"){
		showsubtitle(array('版块','灯色','帖子数量', '回复得分','24小时未受理超时扣分','总得分'));
	}else{
		showsubtitle(array('版块','帖子数量', '回复得分','24小时未受理超时扣分','总得分'));
	}
	


	//$start_limit = ($page - 1) * $perpage;
	//$export_url[] = 'start='.$start_limit;
	foreach ($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== FALSE) {
			$url_add .= '&'.$key.'='.$val;
		}
	}
	//$url = ADMINSCRIPT.'?action=threadkpi&operation=tj&page='.$page.'&perpage='.$perpage.$url_add;
	$count = $sqladd ? C::t('forum_kpilog')->count_by_where($sqladd) : C::t('forum_kpilog')->count();
	if($count) {
		//$multipage = multi($count, $perpage, $page, $url, 0, 3);
		
		
		if($_GET['group_light'] == "是"){
			$groupfield = 'fid ,light';
		}else{
			$groupfield = 'fid';
		}
		
		foreach(C::t('forum_kpilog')->fetch_all_group_by_where($sqladd,$groupfield) as $result) {
			$list[] = $result;
		}
		
		if($_GET['group_light'] == "是"){
			foreach($list AS $key => $val) {
				showtablerow('', array( '', '', '', '', 'class="highlight"', '', '', '', '', '', ''), array(
					"<a href='forum.php?mod=forumdisplay&fid={$val['fid']} target='_blank'>".$_G['cache']['forums'][$val['fid']]['name'].'</a>',
	                $val['light'] ? $val['light'] : '',
	                $val['NUM'],
	                $val['score'],
	                -$val['expired_score'],
	                $val['score'] - $val['expired_score']
				));
			}
		}else{
			foreach($list AS $key => $val) {
				showtablerow('', array( '', '', '', 'class="highlight"', '', '', '', '', '', '', ''), array(
					"<a href='forum.php?mod=forumdisplay&fid={$val['fid']} target='_blank'>".$_G['cache']['forums'][$val['fid']]['name'].'</a>',
	                $val['NUM'],
	                $val['score'],
	                -$val['expired_score'],
	                $val['score'] - $val['expired_score']
				));
			}
			
		}
		
		echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
		showsubmit('threadkpisubmit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=threadkpi&operation=export_tj&'.implode('&', $export_url).'" title="'.$lang['threadkpi_tj_export_title'].'">'.$lang['threadkpi_tj_export'].'</a>', $multipage, false);
	}

	showtablefooter();
	showformfooter();
    
} elseif ($operation == 'export'){

	$sqladd = threadkpisql();
	$_GET['start'] = intval($_GET['start']);
	$count = $sqladd ? C::t('forum_kpilog')->count_by_where($sqladd) : C::t('forum_kpilog')->count();
	if($count) {
		$count = min(10000, $count);
		foreach(C::t('forum_kpilog')->fetch_all_by_where($sqladd, $_GET['start'], $count) as $result) {
			$list[] = $result;
		}
        
        if(!isset($_G['cache']['forums'])) {
            loadcache('forums');
        }
        
        $detail = "帖子ID,帖子标题,版块,状态,回复天数,回复得分,灯色,备注,受理超时,满意度,发帖时间,审核时间,受理时间,回复时间\n";
        
        //file_put_contents("list.txt",print_r($list,true));
		foreach($list as $key => $val) {
            //$detail .= "{$val['tid']},{$val['subject']},".$_G['cache']['forums'][$val['fid']]."\n";
            $d = array(
                'tid' => $val['tid'],
                'subject' => cutstr($val['subject'],30),
                'fid' => $_G['cache']['forums'][$val['fid']]['name'],
                'sortid' => $sortAr['k'.$val['sortid']],
                'day_cnt' => $val['day_cnt'],
                'score' => $val['score'],
                'light' => $val['light'],
                'remark' => $val['remark'],
                'sor_expired' => $val['sor_expired'] ? '是': '否',
                'grade' =>  $gradeAr['k'.$val['grade']],
                'newthread' => $val['newthread'] ? date("Y-m-d",$val['newthread']) : '--',
                'modthread' => $val['modthread'] ? date("Y-m-d",$val['modthread']) : '--',
                'sorthread' => $val['sorthread'] ? date("Y-m-d",$val['sorthread']) : '--',
                'replythread' => $val['replythread'] ? date("Y-m-d",$val['replythread']) : '--'
            );
            
            if($val['sortid'] < 3){
                $d['sor_expired'] = '--';
            }
            
            if($val['sortid'] < 4){
            	$d['replythread'] = '--';
            }
            
            //$detail .= "{$val['tid']}\t{$val['subject']}\t".$_G['cache']['forums'][$val['fid']];
            $detail .= implode(",",array_values($d))."\n";
            
			//$detail .= $detail."\n";
		}

	}
	//$detail = implode(',', $title)."\n".$detail;
	$filename = 'kpilist_'.date('Ymd', TIMESTAMP).'.csv';

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	if($_G['charset'] != 'gbk') {
		$detail = diconv($detail, $_G['charset'], 'GBK');
	}
	echo $detail;
	exit();
} elseif ($operation == 'export_tj'){
    
    $sqladd = threadkpisql();
	$_GET['start'] = intval($_GET['start']);
	$count = $sqladd ? C::t('forum_kpilog')->count_by_where($sqladd) : C::t('forum_kpilog')->count();
	if($count) {
		$count = min(10000, $count);
		
		
		if($_GET['group_light'] == "是"){
			$groupfield = 'fid ,light';
			$detail = "版块,灯色,帖子数量,回复得分,24小时未受理超时扣分,总得分\n";
		}else{
			$groupfield = 'fid';
			$detail = "版块,帖子数量,回复得分,24小时未受理超时扣分,总得分\n";
		}
		
		foreach(C::t('forum_kpilog')->fetch_all_group_by_where($sqladd,$groupfield) as $result) {
			$list[] = $result;
		}
        
        if(!isset($_G['cache']['forums'])) {
            loadcache('forums');
        }
        
        
        if($_GET['group_light'] == "是"){
        	foreach($list as $key => $val) {
	            $d = array(
	                'fid' => $_G['cache']['forums'][$val['fid']]['name'],
	                'light' => $val['light'] ? $val['light'] : '',
	                'num' => $val['NUM'],
	                'score' => $val['score'],
	                'expired_score' => $val['expired_score'],
	                'fscore' => ($val['score'] - $val['expired_score'])
	            );
	            $detail .= implode(",",array_values($d))."\n";
			}
        }else{
        	foreach($list as $key => $val) {
	            $d = array(
	                'fid' => $_G['cache']['forums'][$val['fid']]['name'],
	                'num' => $val['NUM'],
	                'score' => $val['score'],
	                'expired_score' => $val['expired_score'],
	                'fscore' => ($val['score'] - $val['expired_score'])
	            );
	            $detail .= implode(",",array_values($d))."\n";
			}
        }
	}
    
	//$detail = implode(',', $title)."\n".$detail;
	$filename = 'kpitj_'.date('Ymd', TIMESTAMP).'.csv';

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	if($_G['charset'] != 'gbk') {
		$detail = diconv($detail, $_G['charset'], 'GBK');
	}
	echo $detail;
	exit();
    
} else {
	cpmsg('action_noaccess', '', 'error');
}


function threadkpisql() {


	$_GET = daddslashes($_GET);

	$_GET['srch_subject'] = trim($_GET['srch_subject']);

	//$_GET['srch_score_max'] = intval($_GET['srch_score_max']);
	//$_GET['srch_score_min'] = intval($_GET['srch_score_min']);
    
    
    if(!empty($_GET['srch_sortid'])){
        $_GET['srch_sortid'] = intval($_GET['srch_sortid']);
    }
    
	$_GET['srch_newthread_by'] = trim($_GET['srch_newthread_by']);
    $_GET['srch_newthread_start'] = trim($_GET['srch_newthread_start']);
	$_GET['srch_newthread_end'] = trim($_GET['srch_newthread_end']);
    
    $_GET['srch_modthread_by'] = trim($_GET['srch_modthread_by']);
    $_GET['srch_modthread_start'] = trim($_GET['srch_modthread_start']);
	$_GET['srch_modthread_end'] = trim($_GET['srch_modthread_end']);
    
    $_GET['srch_sorthread_by'] = trim($_GET['srch_sorthread_by']);
    $_GET['srch_sorthread_start'] = trim($_GET['srch_sorthread_start']);
	$_GET['srch_sorthread_end'] = trim($_GET['srch_sorthread_end']);
    
    $_GET['srch_replythread_by'] = trim($_GET['srch_replythread_by']);
    $_GET['srch_replythread_start'] = trim($_GET['srch_replythread_start']);
	$_GET['srch_replythread_end'] = trim($_GET['srch_replythread_end']);
    
    if(!empty($_GET['srch_fid'])){
        $_GET['srch_fid'] = intval($_GET['srch_fid']);
    }
    
	$sqladd = '';
	if($_GET['srch_subject']) {
		$sqladd .= " AND subject LIKE '%{$_GET['srch_subject']}%' ";
	}
	
    if($_GET['srch_score_min'] === '0'){
        $sqladd .= " AND score >= '{$_GET['srch_score_min']}'";
    }elseif(!empty($_GET['srch_score_min'])){
        $sqladd .= " AND score >= '".intval($_GET['srch_score_min'])."'";
    }
    
    if($_GET['srch_score_max'] === '0'){
        $sqladd .= " AND score <= '{$_GET['srch_score_max']}'";
    }elseif(!empty($_GET['srch_score_max'])){
        $sqladd .= " AND score <= '".intval($_GET['srch_score_max'])."'";
    }
    
	
    if($_GET['srch_grade'] == '-'){
        $sqladd .= " AND grade = 0";
    }elseif(!empty($_GET['srch_grade'])){
        $sqladd .= " AND grade = {$_GET['srch_grade']}";
    }
    
	if($_GET['srch_sortid']) {
		$sqladd .= " AND sortid = {$_GET['srch_sortid']}";
	}

	if($_GET['srch_newthread_by']) {
		$sqladd .= " AND newthread_by = '{$_GET['srch_newthread_by']}'";
	}
	
	if($_GET['srch_newthread_start'] || $_GET['srch_newthread_end']) {
		if($_GET['srch_newthread_start']) {
			list($y, $m, $d) = explode("-", $_GET['srch_newthread_start']);
			$sqladd .= " AND newthread >= '".mktime('0', '0', '0', $m, $d, $y)."' ";
		}
		if($_GET['srch_newthread_end']) {
			list($y, $m, $d) = explode("-", $_GET['srch_newthread_end']);
			$sqladd .= " AND newthread <= '".mktime('23', '59', '59', $m, $d, $y)."' AND newthread <> 0 ";
		}
	}
    
    
    if($_GET['srch_modthread_by']) {
		$sqladd .= " AND modthread_by = '{$_GET['srch_modthread_by']}'";
	}
    
    if($_GET['srch_modthread_start'] || $_GET['srch_modthread_end']) {
		if($_GET['srch_modthread_start']) {
			list($y, $m, $d) = explode("-", $_GET['srch_modthread_start']);
			$sqladd .= " AND modthread >= '".mktime('0', '0', '0', $m, $d, $y)."' ";
		}
		if($_GET['srch_modthread_end']) {
			list($y, $m, $d) = explode("-", $_GET['srch_modthread_end']);
			$sqladd .= " AND modthread <= '".mktime('23', '59', '59', $m, $d, $y)."' AND modthread <> 0 ";
		}
	}
    
    if($_GET['srch_sorthread_by']) {
		$sqladd .= " AND sorthread_by = '{$_GET['srch_sorthread_by']}'";
	}
    
    if($_GET['srch_sorthread_start'] || $_GET['srch_sorthread_end']) {
		if($_GET['srch_sorthread_start']) {
			list($y, $m, $d) = explode("-", $_GET['srch_sorthread_start']);
			$sqladd .= " AND sorthread >= '".mktime('0', '0', '0', $m, $d, $y)."' ";
		}
		if($_GET['srch_sorthread_end']) {
			list($y, $m, $d) = explode("-", $_GET['srch_sorthread_end']);
			$sqladd .= " AND sorthread <= '".mktime('23', '59', '59', $m, $d, $y)."' AND sorthread <> 0 ";
		}
	}
    
    if($_GET['srch_replythread_start'] || $_GET['srch_replythread_end']) {
		if($_GET['srch_replythread_start']) {
			list($y, $m, $d) = explode("-", $_GET['srch_replythread_start']);
			$sqladd .= " AND replythread >= '".mktime('0', '0', '0', $m, $d, $y)."' ";
		}
		if($_GET['srch_replythread_end']) {
			list($y, $m, $d) = explode("-", $_GET['srch_replythread_end']);
			$sqladd .= " AND replythread <= '".mktime('23', '59', '59', $m, $d, $y)."' AND replythread <> 0 ";
		}
	}
    
    if($_GET['srch_fid']) {
		$sqladd .= " AND fid = '{$_GET['srch_fid']}'";
	}
    
	return $sqladd ? ' 1 '.$sqladd : '';
}
?>