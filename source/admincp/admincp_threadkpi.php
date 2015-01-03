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
if($operation != 'export') {
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
$card_setting = $_G['setting']['card'];


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
            //array('nav_threadkpi_tj', 'threadkpi&operation=tj', $submenu['tj']),
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
		showformheader('threadkpi&operation=set&');
		showtableheader();
		showsetting('threadkpi_config_light_green', 'light_green', '', 'text');
        showsetting('threadkpi_config_light_yellow', 'light_yellow', '', 'text');
        showsetting('threadkpi_config_light_gray', 'light_gray', '', 'text');
        showsetting('threadkpi_config_light_red', 'light_red', '', 'text');
		showsubmit('threadkpisubmit');
		showtablefooter();
		showformfooter();
	} else {
		//C::t('common_setting')->update('card', array('open' => $_POST['card_config_open']));
		//updatecache('setting');
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
			cplang('threadkpi_list_score').cplang('between'), '<input type="text" name="srch_score_min" class="txt" value="'.($_GET['srch_score_min'] ? $_GET['srch_score_min'] : '').'" />- &nbsp;<input type="text" name="srch_score_max" class="txt" value="'.($_GET['srch_score_max'] ? $_GET['srch_score_max'] :'' ).'" />',
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
	showsubtitle(array('帖子ID', '帖子标题', '版块', '状态', '回复天数','灯色', '得分','受理超时','满意度', '发帖时间','审核时间','受理时间','回复时间', '备注'));


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
				$val['sortid'],
				$val['day_cnt'],
				$val['light'],
				$val['score'],
				$val['sor_expired'] ? "超时未受理" : ' -- ',
                $val['grade'],
				$val['newthread'] ? dgmdate($val['newthread']) : ' -- ',
				$val['modthread'] ? dgmdate($val['modthread']) : ' -- ',
				$val['sorthread'] ? dgmdate($val['sorthread']) : ' -- ',
				$val['replythread'] ? dgmdate($val['replythread']) : ' -- ',
                $val['remark']
			));
		}
		echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
		showsubmit('threadkpisubmit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=threadkpi&operation=export&'.implode('&', $export_url).'" title="'.$lang['threadkpi_list_export_title'].'">'.$lang['threadkpi_list_export'].'</a>', $multipage, false);
	}

	showtablefooter();
	showformfooter();

} elseif($operation == 'tj') {
    
    /*
	if(submitcheck('cardsubmit')) {
		if(is_array($_POST['delete'])) {
			C::t('common_card_type')->delete($_POST['delete']);
			C::t('common_card')->update_by_typeid($_POST['delete'], array('typeid'=>1));
		}
		if(is_array($_POST['newtype'])) {
			$_POST['newtype'] = dhtmlspecialchars(daddslashes($_POST['newtype']));
			foreach($_POST['newtype'] AS $key => $val) {
				if(trim($val)) {
					C::t('common_card_type')->insert(array('typename' => trim($val)));
				}
			}
		}
	}
	showtips('card_type_tips');
	showformheader('card&operation=type&');
	showtableheader();
	showtablerow('class="header"', array('', ''), array(
		cplang('delete'),
		cplang('card_type'),
	));

	showtablerow('', '', array(
		'<input class="checkbox" type="checkbox" value ="" disabled="disabled" >',
		cplang('card_type_default'),
	));
	foreach(C::t('common_card_type')->range(0, 0, 'ASC') as $result) {
		showtablerow('', '', array(
		'<input class="checkbox" type="checkbox" name ="delete[]" value ="'.$result['id'].'" >',
		$result['typename'],
		));
	}
	echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1,''], [1,'<input type="text" class="txt" size="30" name="newtype[]">']],
	];
	</script>
EOT;
	echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';
	showsubmit('cardsubmit', 'submit', 'select_all');
	showtablefooter();
	showformfooter();
    */
} elseif ($operation == 'export'){

	$sqladd = threadkpisql();
	$_GET['start'] = intval($_GET['start']);
	$count = $sqladd ? C::t('forum_kpilog')->count_by_where($sqladd) : C::t('forum_kpilog')->count();
	if($count) {
		$count = min(10000, $count);
		foreach(C::t('forum_kpilog')->fetch_all_by_where($sqladd, $_GET['start'], $count) as $result) {
			$list[] = $result;
		}
        $detail = '';
        
		foreach($list as $key => $val) {
            $d = array(
                'tid' => $val['tid'],
                'subject' => $val['subject'],
                'fid' => $_G['cache']['forums'][$val['fid']],
                'sortid' => $sortAr['k'.$val['sortid']],
                'day_cnt' => $val['day_cnt'],
                'light' => $val['light'],
                'score' => $val['score'],
                'sor_expired' => $val['sor_expired'] ? '是': '否',
                'grade' =>  $gradeAr['k'.$val['grade']],
                'newthread' => $val['newthread'] ? date("Ymd",$val['newthread']) : '--',
                'modthread' => $val['modthread'] ? date("Ymd",$val['modthread']) : '--',
                'sorthread' => $val['sorthread'] ? date("Ymd",$val['sorthread']) : '--',
                'replythread' => $val['replythread'] ? date("Ymd",$val['replythread']) : '--'
            );
            
            if($val['sortid'] < 3){
                $d['sor_expired'] = '--';
            }
            
            //$detail .= "{$val['tid']}\t{$val['subject']}\t".$_G['cache']['forums'][$val['fid']];
            $detail .= implode(",",$d);
			$detail .= $detail."\n";
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
} else {
	cpmsg('action_noaccess', '', 'error');
}


function threadkpisql() {


	$_GET = daddslashes($_GET);

	$_GET['srch_subject'] = trim($_GET['srch_subject']);

	$_GET['srch_score_max'] = intval($_GET['srch_score_max']);
	$_GET['srch_score_min'] = intval($_GET['srch_score_min']);
    
    
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
	
	if($_GET['srch_score_min'] && !$_GET['srch_score_max']) {
		$sqladd .= " AND score >= '{$_GET['srch_score_min']}'";
	} elseif($_GET['srch_score_max'] && !$_GET['srch_score_min']) {
		$sqladd .= " AND score <= '{$_GET['srch_score_max']}'";
	} elseif($_GET['srch_score_min'] && $_GET['srch_score_max']) {
		$sqladd .= " AND score between '{$_GET['srch_score_min']}' AND '{$_GET['srch_score_max']}'";
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