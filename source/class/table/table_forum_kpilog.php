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

class table_forum_kpilog extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_kpilog';
		$this->_pk    = 'tid';

		parent::__construct();
	}
    
    public function update_grade_by_tid($tid, $grade) {
		$tid = dintval($tid, true);
        $grade = dintval($grade, true);
        
        return DB::query("UPDATE ".DB::table($this->_table)." SET grade=%d WHERE tid=%d", array($grade, $tid));
	}
    
    public function update_by_tid($tid, $data) {
		$tid = dintval($tid, true);
		if($data && is_array($data) && $tid) {
			return DB::update($this->_table, $data, DB::field('tid', $tid));
		}
		return array();
	}
    
    public function count_by_where($where) {
		return ($where = (string)$where) ? DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).' WHERE '.$where) : 0;
	}
    
    public function fetch_all_by_where($where, $start = 0, $limit = 0) {
		$where = $where ? ' WHERE '.(string)$where : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY tid DESC'.DB::limit($start, $limit));
	}
    
}

?>