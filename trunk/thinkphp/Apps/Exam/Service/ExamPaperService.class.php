<?php
namespace  Exam\Service;

class ExamPaperService extends AbstractService {

	protected $_detail_model;
	protected $_dempartment_model;

	// 构造方法
	public function __construct() {
		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Exam/ExamPaper");
		$this->_detail_model = D('Exam/ExamPaperDetail');
		$this->_dempartment_model = D('Common/CommonDepartment');
	}

	public function get_by_id($id) {
		$paper = $this->get($id);
		$paper['departments'] = array();
		if(!empty($paper['cd_ids'])) {
			$conditions = array(
				'cd_id' => explode(',', $paper['cd_ids']),
			);

			$departments =  $this->_dempartment_model->list_by_conds($conditions);
			foreach ($departments as $value) {
				$paper['departments'][] = $value['cd_name'];
			}
		}

		if(!empty($paper['rules'])) {
			$paper['rules'] = unserialize($paper['rules']);
		}

		return $paper;
	}
	/**
	 * 获取即将开始的试卷
	 * @return array
	 */
	public function list_started_papers() {
		return $this->_d->list_started_papers();
	}
	/**
	 * 获取即将结束的试卷
	 * @return array
	 */
	public function list_stoped_papers() {
		return $this->_d->list_stoped_papers();
	}
	/**
	 * 获取已结束的试卷
	 * @return array
	 */
	public function list_end_papers() {
		return $this->_d->list_end_papers();
	}
	/**
	 * 更新试卷提醒状态
	 * @return array
	 */
	public function update_flag_by_paperids($paperids, $flag) {
		return $this->_d->update_by_conds(array('id' => $paperids), array('flag' => $flag));
	}
	
	/**
	 * 获取未完成试卷记录数
	 * @return array
	 */
	public function count_by_uncomplete() {
		return $this->_d->count_by_uncomplete();
	}

	public function list_uncompletes() {
		return $this->_d->list_uncompletes();
	}
}