<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadtype.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadgrade extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_threadgrade';
		$this->_pk    = 'gid';

		parent::__construct();
	}
    
    public function fetch_grade_by_tid($tid){
        if(!empty($tid)) {
			$where = ' WHERE '.DB::field('tid', $tid);
		}
        return DB::fetch_all("SELECT * FROM ".DB::table('forum_threadgrade')." $where ".DB::limit(0, 1));
    }
}

?>