<?php
namespace Exam\Model;

class ExamTjModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}

	public function list_by_status($status, $uid) {
		
		if($status==0){
			$sql = "SELECT a.id, a.paper_id, a.paper_name, a.begin_time, a.end_time, CASE WHEN a.end_time>".NOW_TIME." THEN 1 ELSE 0 END as is_over, b.status as paper_status FROM __TABLE__ a LEFT JOIN oa_exam_paper b ON a.paper_id=b.id WHERE a.status<2 AND a.m_uid=? AND b.status=1 ORDER BY is_over DESC, a.begin_time DESC";
		}else{
			$sql = "SELECT a.id, a.paper_id, a.paper_name, a.my_score, a.my_is_pass, a.my_begin_time+a.my_time*60 as my_end_time FROM __TABLE__ a LEFT JOIN oa_exam_paper b ON a.paper_id=b.id WHERE a.status=2 AND a.m_uid=? AND b.status=1 ORDER BY my_end_time DESC";
		}
		
		$params = array($uid);
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

	/**
     * 获取试卷信息，关联paper表
     * @param $id 试卷id
	 * @return array
     */

	public function get_with_paper_by_id($id, $paper_id=0, $uid=0) {
		if($id==0){
			$where=" a.paper_id=? AND a.m_uid=?";
			$params = array($paper_id, $uid);
		}else{
			$where=" a.id=?";
			$params = array($id);
		}
		$sql = "SELECT a.*, b.rules, b.cd_ids, b.m_uids, b.type, b.tiku, b.cover_id, b.status as paper_status FROM __TABLE__ a LEFT JOIN oa_exam_paper b ON a.paper_id=b.id WHERE a.status<3 AND b.status<3 AND $where";
		
		$result = $this->_m->fetch_row($sql, $params);
		return $result;
	}

	/**
     * 获取考试结束、已开始考试、未交卷列表
     * @param $paper_id 试卷id
	 * @return array
     */
	public function list_by_notsubmit($paper_id) {
		$sql = "SELECT id, m_uid FROM __TABLE__ WHERE status=1 AND paper_id=?";
		$params = array($paper_id);
		return $this->_m->fetch_array($sql, $params);
	}
}
