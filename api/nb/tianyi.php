<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: uc.php 34214 2013-11-11 02:33:40Z hypowang $
 */

error_reporting(0);

define('IN_API', true);
define('CURSCRIPT', 'api');
define('APP_KEY','dac4bb4e44aa0d04a54079178692c3b0');
define('DEBUG',false);
/*
echo strlen(APP_KEY);
echo md5(mt_rand());
*/

require_once '../../source/class/class_core.php';
require_once '../../source/function/function_core.php';

$discuz = C::app();
$discuz->init();

require_once DISCUZ_ROOT.'./config/config_ucenter.php';

$action = gpc('action','GP','');
require_once libfile('function/forum');
require_once libfile('function/discuzcode');
/**
 *  
 */
$fid = gpc('fid','GP',2);
$api = new api_ty($fid);
if(in_array($action, array('addthread', 'getthread'))) {
    $api->$action();
} else {
    $api->failed('method_not_found');
}

class api_ty {
    private $_codeList =  array(
        'success' => '请求成功',
        'post_newthread_succeed' => '发帖成功',
        'username_empty' => '用户名为空',
        'hash_verify_failed' => '数据校验失败',
        'paramter_missing' => '参数错误',
        'external_id_lost' => '外部ID缺失',
        'external_id_datatypeerror' => '外部ID数据格式错误',
        'method_not_found' => '方法不存在',
        'user_not_found' => '用户不存在',
        'post_subject_toolong' => '标题过长',
        'post_message_tooshort' => '内容过短',
        'post_flood_ctrl' => '频繁提交限制',
        'thread_flood_ctrl_threads_per_hour' => '时间段提交限制',
        'forum_not_found' => '无该板块',
        'thread_not_found' => '帖子未找到',
        'thread_close_or_inmod' => '帖子审核中或者已经关闭或者已经删除',
        'thread_unreplied' => '帖子尚未被回复',
        'get_thread_succeed' => '获取回复数据成功',
        'unknow' => '未知错误'
    );
    
    private $param = array();
    
    private $tid = 0;
    private $pid = 0;
    private $forum = array();
    private $member;
    
    public function __construct($fid) {
        global $_G;
        
        loadforum($fid);
        
        if(!$_G['forum']){
            $this->respone('forum_not_found');
        }
        
        $this->forum = $_G['forum'];
    }
    
    protected function _init_parameters($parameters){

		$varname = array(
			'member', 'group', 'forum', 'extramessage',
			'subject', 'sticktopic', 'save', 'ordertype', 'hiddenreplies',
			'allownoticeauthor', 'readperm', 'price', 'typeid', 'sortid',
			'publishdate', 'digest', 'moderated', 'tstatus', 'isgroup', 'imgcontent', 'imgcontentwidth',
			'replycredit', 'closed', 'special', 'tags',
			'message','clientip', 'invisible', 'isanonymous', 'usesig',
			'htmlon', 'bbcodeoff', 'smileyoff', 'parseurloff', 'pstatus', 'geoloc',
		);
		foreach($varname as $name) {
			if(!isset($this->param[$name]) && isset($parameters[$name])) {
				$this->param[$name] = $parameters[$name];
			}
		}

	}
    
    
    
    public function addthread(){
        global $_G;
        
        $this->param['username'] = gpc('username','GP','');
        $this->param['subject'] = gpc('subject','GP','');
        $this->param['content'] = gpc('content','GP','');
        $this->param['external_id'] = (int)gpc('external_id','GP',0);
        
        $hash = gpc('hash','GP','');
        
        if($hash != md5(APP_KEY.$this->param['username'].$this->param['subject'].$this->param['content'].$this->param['external_id'])){
            if(!DEBUG){
                $this->respone('hash_verify_failed');
            }
        }
        
        if(empty($this->param['username'])){
            $this->respone('username_empty');
        }
        
        $user = C::t('common_member')->fetch_by_username($this->param['username']);
        if(empty($user)){
            $this->respone('user_not_found');
        }
        
        
        if(empty($this->param['external_id'])){
            $this->respone('external_id_lost');
        }
        
        if(preg_match("/^\d+$/",$this->param['external_id'])){
            
            $info = C::t('forum_thread_interface')->fetch_by_eid($this->param['external_id']);
            if($info){
                /**
                 * 如果已经存在 ,则直接返回成功
                 */
                $this->respone(array('code' => 'success','message' => $this->_codeList['post_newthread_succeed']),array('tid' => $info['tid']));
            }
        }else{
            $this->respone('external_id_datatypeerror');
        }
        
        $this->member = $user;
        /**
         * 添加逻辑 
         */
        $params = array(
            'subject' => $this->param['subject'],
            'message' => $this->param['content'],
            'typeid' => 0,
            'sortid' => 0
        );
        
        $_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;

        if ($_G['group']['allowsetpublishdate'] && $_GET['cronpublish'] && $_GET['cronpublishdate']) {
            $publishdate = strtotime($_GET['cronpublishdate']);
            if ($publishdate > $_G['timestamp']) {
                $_GET['save'] = 1;
            } else {
                $publishdate = $_G['timestamp'];
            }
        } else {
            $publishdate = $_G['timestamp'];
        }
        $params['publishdate'] = $publishdate;

        
        require_once libfile('function/post');
        $this->tid = $this->pid = 0;
        
		$this->_init_parameters($params);

		if(trim($this->param['subject']) == '') {
			$this->respone('paramter_missing');
		}

		if(!$this->param['sortid'] && !$this->param['special'] && trim($this->param['message']) == '') {
			$this->respone('paramter_missing');
		}
        
        $this->param['modnewthreads'] = 1;
        
		if(($post_invalid = checkpost($this->param['subject'], $this->param['message'], ($this->param['special'] || $this->param['sortid'])))) {
			$this->respone($post_invalid, array('minpostsize' => $this->setting['minpostsize'], 'maxpostsize' => $this->setting['maxpostsize']));
		}

		if(checkflood()) {
			$this->respone('post_flood_ctrl', array('floodctrl' => $this->setting['floodctrl']));
		} elseif(checkmaxperhour('tid')) {
			$this->respone('thread_flood_ctrl_threads_per_hour',array('threads_per_hour' => $this->group['maxthreadsperhour']));
		}
		$this->param['save'] = $this->member['uid'] ? $this->param['save'] : 0;
		$this->param['displayorder'] = -2 ;
		
        if($this->param['displayorder'] == -2) {
			C::t('forum_forum')->update($this->forum['fid'], array('modworks' => '1'));
		}

		$this->param['digest'] = 0;
		$this->param['readperm'] = 0;
		$this->param['isanonymous'] = 0;
		$this->param['price'] = 0;
        $this->param['price'] = 0;
        $this->param['typeid'] = 0;
        $this->param['sortid'] = 0;
        $this->param['special'] = 0;
		$this->param['typeexpiration'] = 0;
		$author = $this->member['username'];
		$this->param['moderated'] = 0;
        
		$this->param['ordertype'] && $this->param['tstatus'] = setstatus(4, 1, $this->param['tstatus']);
		$this->param['imgcontent'] && $this->param['tstatus'] = setstatus(15, $this->param['imgcontent'], $this->param['tstatus']);
		$this->param['hiddenreplies'] && $this->param['tstatus'] = setstatus(2, 1, $this->param['tstatus']);

		$this->param['allownoticeauthor'] && $this->param['tstatus'] = setstatus(6, 1, $this->param['tstatus']);
		$this->param['isgroup'] = $this->forum['status'] == 3 ? 1 : 0;

		$this->param['publishdate'] = !$this->param['modnewthreads'] ? $this->param['publishdate'] : TIMESTAMP;

		$newthread = array(
			'fid' => $this->forum['fid'],
			'posttableid' => 0,
			'readperm' => $this->param['readperm'],
			'price' => $this->param['price'],
			'typeid' => $this->param['typeid'],
			'sortid' => $this->param['sortid'],
			'author' => $author,
			'authorid' => $this->member['uid'],
			'subject' => $this->param['subject'],
			'dateline' => $this->param['publishdate'],
			'lastpost' => $this->param['publishdate'],
			'lastposter' => $author,
			'displayorder' => $this->param['displayorder'],
			'digest' => $this->param['digest'],
			'special' => $this->param['special'],
			'attachment' => 0,
			'moderated' => $this->param['moderated'],
			'status' => $this->param['tstatus'],
			'isgroup' => $this->param['isgroup'],
			'replycredit' => $this->param['replycredit'],
			'closed' => $this->param['closed'] ? 1 : 0
		);
		$this->tid = C::t('forum_thread')->insert($newthread, true);
		C::t('forum_newthread')->insert(array(
		    'tid' => $this->tid,
		    'fid' => $this->forum['fid'],
		    'dateline' => $this->param['publishdate'],
		));
		useractionlog($this->member['uid'], 'tid');

		if ($this->param['publishdate'] != TIMESTAMP) {
			$cron_publish_ids = dunserialize($this->cache('cronpublish'));
			$cron_publish_ids[$this->tid] = $this->tid;
			$cron_publish_ids = serialize($cron_publish_ids);
			savecache('cronpublish', $cron_publish_ids);
		}

		if(!$this->param['isanonymous']) {
			C::t('common_member_field_home')->update($this->member['uid'], array('recentnote'=>$this->param['subject']));
		}

		if($this->param['moderated']) {
			updatemodlog($this->tid, ($this->param['displayorder'] > 0 ? 'STK' : 'DIG'));
			updatemodworks(($this->param['displayorder'] > 0 ? 'STK' : 'DIG'), 1);
		}

		$this->param['bbcodeoff'] = checkbbcodes($this->param['message'], !empty($this->param['bbcodeoff']));
		$this->param['smileyoff'] = checksmilies($this->param['message'], !empty($this->param['smileyoff']));
		$this->param['parseurloff'] = !empty($this->param['parseurloff']);
		$this->param['htmlon'] = $this->group['allowhtml'] && !empty($this->param['htmlon']) ? 1 : 0;
		$this->param['usesig'] = !empty($this->param['usesig']) && $this->group['maxsigsize'] ? 1 : 0;
		$class_tag = new tag();
		$this->param['tagstr'] = $class_tag->add_tag($this->param['tags'], $this->tid, 'tid');


		$this->param['pinvisible'] = $this->param['modnewthreads'] ? -2 : (empty($this->param['save']) ? 0 : -3);
		$this->param['message'] = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $this->param['message']);

		$this->param['pstatus'] = intval($this->param['pstatus']);
		defined('IN_MOBILE') && $this->param['pstatus'] = setstatus(4, 1, $this->param['pstatus']);

		if($this->param['imgcontent']) {
			stringtopic($this->param['message'], $this->tid, true, $this->param['imgcontentwidth']);
		}
        
		$this->pid = insertpost(array(
			'fid' => $this->forum['fid'],
			'tid' => $this->tid,
			'first' => '1',
			'author' => $this->member['username'],
			'authorid' => $this->member['uid'],
			'subject' => $this->param['subject'],
			'dateline' => $this->param['publishdate'],
			'message' => $this->param['message'],
			'useip' => $this->param['clientip'] ? $this->param['clientip'] : getglobal('clientip'),
			'port' => $this->param['remoteport'] ? $this->param['remoteport'] : getglobal('remoteport'),
			'invisible' => $this->param['pinvisible'],
			'anonymous' => $this->param['isanonymous'],
			'usesig' => $this->param['usesig'],
			'htmlon' => $this->param['htmlon'],
			'bbcodeoff' => $this->param['bbcodeoff'],
			'smileyoff' => $this->param['smileyoff'],
			'parseurloff' => $this->param['parseurloff'],
			'attachment' => '0',
			'tags' => $this->param['tagstr'],
			'replycredit' => 0,
			'status' => $this->param['pstatus']
		));

		$statarr = array(0 => 'thread', 1 => 'poll', 2 => 'trade', 3 => 'reward', 4 => 'activity', 5 => 'debate', 127 => 'thread');
		include_once libfile('function/stat');
		updatestat($this->param['isgroup'] ? 'groupthread' : $statarr[$this->param['special']]);


		if($this->param['geoloc'] && IN_MOBILE == 2) {
			list($mapx, $mapy, $location) = explode('|', $this->param['geoloc']);
			if($mapx && $mapy && $location) {
				C::t('forum_post_location')->insert(array(
					'pid' => $this->pid,
					'tid' => $this->tid,
					'uid' => $this->member['uid'],
					'mapx' => $mapx,
					'mapy' => $mapy,
					'location' => $location,
				));
			}
		}

		if($this->param['modnewthreads']) {
			updatemoderate('tid', $this->tid);
			C::t('forum_forum')->update_forum_counter($this->forum['fid'], 0, 0, 1);
			manage_addnotify('verifythread');
            
		} else {

			if($this->param['displayorder'] != -4) {
				if($this->param['digest']) {
					updatepostcredits('+',  $this->member['uid'], 'digest', $this->forum['fid']);
				}
				updatepostcredits('+',  $this->member['uid'], 'post', $this->forum['fid']);
				if($this->param['isgroup']) {
					C::t('forum_groupuser')->update_counter_for_user($this->member['uid'], $this->forum['fid'], 1);
				}

				$subject = str_replace("\t", ' ', $this->param['subject']);
				$lastpost = "$this->tid\t".$subject."\t".TIMESTAMP."\t$author";
				C::t('forum_forum')->update($this->forum['fid'], array('lastpost' => $lastpost));
				C::t('forum_forum')->update_forum_counter($this->forum['fid'], 1, 1, 1);
				if($this->forum['type'] == 'sub') {
					C::t('forum_forum')->update($this->forum['fup'], array('lastpost' => $lastpost));
				}
			}

			if($this->param['isgroup']) {
				C::t('forum_forumfield')->update($this->forum['fid'], array('lastupdate' => TIMESTAMP));
				require_once libfile('function/grouplog');
				updategroupcreditlog($this->forum['fid'], $this->member['uid']);
			}

			C::t('forum_sofa')->insert(array('tid' => $this->tid,'fid' => $this->forum['fid']));
            
		}
        C::t('forum_thread_interface')->insert(array('tid' => $this->tid,'external_id' => $this->param['external_id'],'dateline' => TIMESTAMP));
        $this->respone(array('code' => 'success','message' => $this->_codeList['post_newthread_succeed']),array('tid' => $this->tid));
        
    }
    
    public function getthread(){
        global $_G;
        
        $tid = (int)gpc('tid','GP',0);
        if(!$tid){
            $this->respone('paramter_missing');
        }
        
        $thread = C::t('forum_thread')->fetch($tid);
        if(!$thread){
            $this->respone('thread_not_found');
        }
        
        if($thread['displayorder'] < 0){
            $this->respone('thread_close_or_inmod');
        }
        
        if($thread['sortid'] != 4){
            $this->respone('thread_unreplied');
        }
        
        $info = C::t("forum_poststick")->fetch_by_tid_priority($thread['tid'],2);
        if(empty($info)){
            $this->respone('thread_unreplied');
        }
        
        $replay = C::t('forum_post')->fetch_all_by_tid_position(0,$thread['tid'],$info['position']);
        $post = $replay[0];
        
        loadforum($thread['fid']);
        //print_r($_G['forum']);
        $t = array(
            'tid' => $thread['tid'],
            'fid' => $thread['fid'],
            'fname' => $_G['forum']['name'],
            'typeid' => $thread['typeid'],
            'subject' => $thread['subject'],
            'author' => $thread['author'],
            'authorid' => $thread['authorid'],
            'dateline' => $thread['dateline']
        );
        
        $post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], 1, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload'], $post['dbdateline'], $post['first']);
        $post['message'] = preg_replace('/(<ignore_js_op>.*<\/ignore_js_op>)/is', '', $post['message']);
        $post['message'] = preg_replace('/<img(.*?)src="(static\/image\/)/','/<img\1 src="'.$_G['siteurl'].'\2',$post['message']);
        $post['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $post['message']);
        
        //echo $post['message'];
        $r = array(
            'pid' => $post['pid'],
            'author' => $post['author'],
            'authorid' => $post['authorid'],
            'dateline' => $post['dateline'],
            'message' => $post['message']
        );
        
        $this->respone(array('code' => 'success', 'message' => $this->_codeList['get_thread_succeed']),array('thread' => $t,'reply' => $r));
    }
    
    
    function failed($code){
        $this->respone($code);
    }
    
    public function showmessage($code){
        $this->respone($code);
    }
    
    function respone($pcode, $data = array()){
        header("Content-Type:application/json; charset=utf-8");
        
        if(is_array($pcode)){
            $code = $pcode['code'];
            $message = $pcode['message'];
        }else{
            $code = $pcode;
            $message = '';
        }
        
        if(empty($code) || empty($this->_codeList[$code])){
            $code = 'unknow';
        }
        
        if(!$message){
            $message = !empty($this->_codeList[$code]) ? $this->_codeList[$code] : $this->_codeList[$code];
        }
        $rt = array('code' => $code , 'message' =>  $message , 'data' => $data);
        
        if(DEBUG){
            print_r($rt);
        }
        echo json_encode($rt);
        exit();
    }
}

function defend_xss($val){
	return is_array($val) ? $val : htmlspecialchars($val);
}


function gpc($name,$w = 'GPC',$default = '',$d_xss=0){
	$i = 0;
	for($i = 0; $i < strlen($w); $i++) {
		if($w[$i] == 'G' && isset($_GET[$name])) return $d_xss ? defend_xss($_GET[$name]) : $_GET[$name];
		if($w[$i] == 'P' && isset($_POST[$name])) return $d_xss ? defend_xss($_POST[$name]) : $_POST[$name];
		if($w[$i] == 'C' && isset($_COOKIE[$name])) return $d_xss ? defend_xss($_COOKIE[$name]) : $_COOKIE[$name];
	}
	return $default;
}