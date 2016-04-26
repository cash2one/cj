<?php
namespace  Exam\Service;

class ExamPaperDetailService extends AbstractService {

	protected $_ti_model;

	// 构造方法
	public function __construct() {
		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Exam/ExamPaperDetail");
		$this->_ti_model = D("Exam/ExamTi");
	}

	public function list_by_paperid($paperid) {
		return $this->_d->list_by_conds(array('paper_id' => $paperid), null, array('orderby' => 'asc'));
	}

	public function list_answer_by_paperid($paperid) {
		$details = $this->list_by_paperid($paperid);
		$tids = array();
		foreach ($details as $detail) {
			$tids[] = $detail['ti_id'];
		}
		$tis = $this->_ti_model->list_by_pks($tids); // bugfix,$tis的下标不是id
		$tis_index=array();
		foreach ($tis as $key => $value) {
			$tis_index[$value['id']]=$value;
		}


		$answers = array();
		foreach ($details as $detail) {
			$answers[$detail['ti_id']] = array(
				'score' => $detail['score'],
				'answer' => $tis_index[$detail['ti_id']]['answer'],
				'type' => $tis_index[$detail['ti_id']]['type']
			);
		}

		return $answers;
	}

	public function list_with_ti_by_paperid($paperid) {
		return $this->_d->list_with_ti_by_paperid($paperid);
	}
}