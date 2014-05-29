<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: table_forum_poststick.php 27806 2012-02-15 03:20:46Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_thread_holiday extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_thread_holiday';
		$this->_pk	  = '';

		parent::__construct();
	}

	public function delete_by_tid_action($tids,$action) {
		if(empty($tids)) {
			return false;
		}
        $wherearr = array();
		$wherearr[] = is_array($tids) ? 'tid IN(%n)' : 'tid=%d';
        $wherearr[] = 'action=%s';
        
		return DB::query('DELETE FROM %t WHERE '.implode(' AND ',$wherearr), array($this->_table,$tids,$action));
	}
	
    
    public function fetch_all_by_tid($tid, $action = '', $start = 0, $limit = 0) {
		$tid = dintval($tid, true);
		$parameter = array($this->_table, $tid);
		$wherearr = array();
		$wherearr[] = is_array($tid) && $tid ? 'tid IN(%n)' : 'tid=%d';
		if($action) {
			$parameter[] = $action;
			$wherearr[] = is_array($action) && $action ? 'action IN(%n)' : 'action=%s';
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all("SELECT * FROM %t $wheresql ".DB::limit($start, $limit), $parameter);
	}
    
    
}

?>