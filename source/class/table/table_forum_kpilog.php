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
	
	
	public function update_light_by_tid($tid , $nowts , $data){
		$thread = $this->fetch_all_by_where("tid = {$tid}" );
		
		//print_r($thread);
        //红灯更新逻辑,超过8小时并且列表被访问到时自动更新, 暂时借用  replythread 这个字段进行更新
		if(( $nowts - $thread[0]['replythread'] ) >= 28800){
			$data['replythread'] = $nowts;
			$this->update_by_tid($tid , $data);
		}
		
	}
	
    
    public function count_by_where($where) {
		return ($where = (string)$where) ? DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).' WHERE '.$where) : 0;
	}
	
    
    public function fetch_all_by_where($where, $start = 0, $limit = 0) {
		$where = $where ? ' WHERE '.(string)$where : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY tid DESC'.DB::limit($start, $limit));
	}
    
    public function fetch_all_group_by_where($where,$groupfield = 'fid ,light') {
		$where = $where ? ' WHERE '.(string)$where : '';
		return DB::fetch_all('SELECT COUNT(tid) AS NUM, '.$groupfield.' , SUM(score) AS score, SUM(sor_expired) AS expired_score FROM '.DB::table($this->_table).$where.' GROUP BY '.$groupfield.'  ORDER BY fid ASC,NUM DESC');
	}
	
	public function delete_by_tids($tids,$unbuffered = false, $limit = 0){
		return DB::delete($this->_table, DB::field('tid', $tids), $limit, $unbuffered);
	}
	
}
?>