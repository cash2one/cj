<?php
namespace Exam\Model;

class ExamPaperModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}

	// 获取即将开始的试卷
	public function list_started_papers() {
		$sql = "SELECT id, notify_begin, begin_time, name, intro, is_all, cd_ids, m_uids, cover_id FROM __TABLE__ WHERE is_notify=1 AND status=1 AND flag=0";
		$params = array();
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

	// 获取即将结束的试卷
	public function list_stoped_papers() {
		$sql = "SELECT id, notify_end, end_time, name, intro, is_all, cd_ids, m_uids, cover_id FROM __TABLE__ WHERE flag=1";
		$params = array();
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

	/**
	 * 获取已结束的试卷
	 * @return array
	 */
	public function list_end_papers() {
		$sql = "SELECT id, name, intro, cover_id, pass_score, paper_time FROM __TABLE__ WHERE status=1 AND end_time<? AND flag<>3";
		$params = array(NOW_TIME);
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

	public function list_uncompletes() {
		$sql = "SELECT * FROM __TABLE__ WHERE status=1 AND end_time>?";
		$params = array(NOW_TIME);

		$result = $this->_m->fetch_array($sql, $params);

		return $result;
	}
}
