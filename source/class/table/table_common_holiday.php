<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_holiday.php 27846 2012-02-15 09:04:33Z linbo.chen $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_holiday extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_holiday';
		$this->_pk    = 'date_key';

		parent::__construct();
	}


	public function fetch_all_by_where($where, $start = 0, $limit = 0) {
		$where = $where ? ' WHERE '.(string)$where : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY date_key DESC'.DB::limit($start, $limit));
	}
    
    public function fetch_by_year($year){
        $where = " WHERE year = {$year}";
        return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY date_key DESC');
    }
    
    public function delete_by_year($year) {
		if(empty($year)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('year', $year), array($this->_table));
	}
}

?>