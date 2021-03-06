<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_regcode extends discuz_table_archive
{
    public function __construct() {

		$this->_table = 'common_member_regcode';
		$this->_pk    = 'coid';

		parent::__construct();
	}
    
    public function fetch_info_by_phone_code($phone, $code){
        
        if(($phone!='') && ($code!='')) {
			$where = ' WHERE '.DB::field('mobile', $phone).' AND '.DB::field('code', $code);
            return DB::fetch_all("SELECT * FROM ".DB::table($this->_table)." $where ".' ORDER BY '.DB::order('dateline', 'DESC').' '.DB::limit(0, 1));
		} else {
            return false;
        }
        
    }
    
    public function delete_expired_data($where,$limit = 100){
        $res = DB::query("DELETE FROM ".DB::table($this->_table)." WHERE $where LIMIT $limit");
    }
    
    public function delete_by_phone($phone, $limit) {
        $where = ' WHERE '.DB::field('mobile', $phone);
        $res = DB::query("DELETE FROM ".DB::table($this->_table)." $where LIMIT $limit");
        return $res;
    }
    
    public function count_by_phone_second($phone , $timestamp){
        return DB::result_first("SELECT COUNT(*) AS num FROM ".DB::table($this->_table)." WHERE mobile=%s AND dateline >=%d", array($phone, $timestamp));
    }
}
    