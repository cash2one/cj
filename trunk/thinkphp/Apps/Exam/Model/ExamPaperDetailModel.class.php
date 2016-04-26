<?php
namespace Exam\Model;

class ExamPaperDetailModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}

	public function list_with_ti_by_paperid($paperid) {
		$sql = "SELECT b.id, b.type, b.title, b.score, b.options FROM __TABLE__ a LEFT JOIN oa_exam_ti b ON a.ti_id=b.id WHERE a.status<2 AND a.paper_id=? ORDER BY a.orderby ASC";
		
		$params = array($paperid);
		return $this->_m->fetch_array($sql, $params);
	}

}
