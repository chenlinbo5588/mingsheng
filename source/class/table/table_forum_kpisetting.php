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

class table_forum_kpisetting extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_kpisetting';
		$this->_pk    = 'id';

		parent::__construct();
	}
    
    
    
    public function update_by_id($id, $data) {
		$id = dintval($id, true);
		if($data && is_array($data) && $id) {
			return DB::update($this->_table, $data, DB::field('id', $id));
		}
		return array();
	}
    
    public function count_by_where($where) {
		return ($where = (string)$where) ? DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).' WHERE '.$where) : 0;
	}
    
    
    public function fetch_by_id($id) {
        $id = dintval($id, true);
		return DB::fetch_first('SELECT * FROM '.DB::table($this->_table).'  WHERE id = '.$id);
	}
    
}

?>