<?php
namespace  Exam\Service;

class ExamTiTjService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Exam/ExamTiTj");
	}

	public function insert_tj($uid, $paperid, $tj_id, $tj) {
		$data = array();
		foreach ($tj as $v) {
			$data[] = array(
				'm_uid' => $uid,
				'paper_id' => $paperid,
				'ti_id' => $v['id'],
				'tj_id' => $tj_id
			);
		}

		return $this->_d->insert_all($data);
	}

	public function update_all($tj_id, $tj) {
		$data = array();
		$conds=array('tj_id'=>$tj_id);
		foreach ($tj as $ti_id => $v) {
			$data = array(
				'is_pass' => $v['is_pass'],
				'my_answer' => $v['my_answer']
			);
			$conds['ti_id']=$ti_id;
			$this->_d->update_by_conds($conds, $data);
		}
	}

	public function update_answer($ti_id, $tj_id, $my_anwser) {
		$data = array( 
			'my_answer' => $my_anwser
		);
		$conds = array('ti_id' => $ti_id, 'tj_id' => $tj_id);
		return $this->_d->update_by_conds($conds, $data);
	}

	public function list_by_paper_id($paperid, $uid) {
		$tjs = $this->_d->list_by_conds(array(
			'paper_id' => $paperid,
			'm_uid' => $uid
		));

		$return = array();
		foreach ($tjs as $tj) {
			$return[$tj['ti_id']] = array(
				'is_pass' => $tj['is_pass'],
				'my_answer' => $tj['my_answer'],
			);
		}

		return $return;
	}

	public function list_by_tj_id($id) {
		$tjs = $this->_d->list_by_conds(array(
			'tj_id' => $id
		),null, array('id'=>'ASC'));

		$return = array();
		foreach ($tjs as $tj) {
			$return[$tj['ti_id']] = array(
				'is_pass' => $tj['is_pass'],
				'my_answer' => $tj['my_answer'],
			);
		}

		return $return;
	}
	
	public function list_with_ti_by_tj_id($tj_id) {
		return $this->_d->list_with_ti_by_tj_id($tj_id);
	}

	/**
     * 根据tj_id获取列表
     * @param $tj_ids 试卷id
	 * @return array
     */

	public function list_by_tj_ids($tj_ids) {
		return $this->_d->list_by_tj_ids($tj_ids);
	}
}