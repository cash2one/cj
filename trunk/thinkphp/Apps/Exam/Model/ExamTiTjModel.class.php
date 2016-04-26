<?php
namespace Exam\Model;

class ExamTiTjModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}

	public function list_with_ti_by_tj_id($tj_id) {
		$sql = "SELECT a.my_answer, a.is_pass, b.answer, b.type, b.title, b.score, b.options FROM __TABLE__ a LEFT JOIN oa_exam_ti b ON a.ti_id=b.id WHERE a.status<2 AND a.tj_id=? ORDER BY a.id ASC";
		
		$params = array($tj_id);
		return $this->_m->fetch_array($sql, $params);
	}

	/**
     * 根据tj_id获取列表
     * @param $tj_ids 试卷id
	 * @return array
     */
	public function list_by_tj_ids($tj_ids) {
		$sql = "SELECT a.id, a.ti_id, a.paper_id, a.tj_id, a.my_answer, b.answer, b.type, b.score FROM __TABLE__ a LEFT JOIN oa_exam_ti b ON a.ti_id=b.id WHERE a.status<2 AND a.tj_id IN(?)";
		$params = array($tj_ids);
		return $this->_m->fetch_array($sql, $params);
	}

}