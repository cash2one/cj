<?php
namespace  Exam\Service;

class ExamTjService extends AbstractService {

	protected $_dempartment_model;
	// 构造方法
	public function __construct() {

		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Exam/ExamTj");
		$this->_dempartment_model = D('Common/CommonDepartment');
		$this->_member_model = D('Common/Member', 'Service');
	}

	public function insert_tj($uid, $paper, $error_num, $score, $use_time) {
		$data = array( 
			'm_uid' => $uid,
			'paper_id' => $paper['id'],
			'paper_name' => $paper['name'],
			'total_score' => $paper['total_score'],
			'pass_score' => $paper['pass_score'],
			'ti_num' => $paper['ti_num'],
			'begin_time' => $paper['begin_time'],
			'end_time' => $paper['end_time'],
			'departments' => implode(' ', $paper['departments']),
			'intro' => $paper['intro'],
			'my_score' => $score,
			'my_time' => $use_time,
			'my_error_num' => $error_num,
			'my_is_pass' => $score >= $paper['pass_score'] ? 1 : 0
		);

		return $this->_d->insert($data);
	}

	public function update_tj($id, $paper, $error_num, $score, $use_time, $status) {
		$data = array( 
			'my_score' => $score,
			'my_time' => $use_time,
			'my_error_num' => $error_num,
			'my_is_pass' => $score >= $paper['pass_score'] ? 1 : 0,
			'status' => $status
		);

		$conds = array('id' => $id);
		return $this->_d->update_by_conds($conds, $data);
	}


	public function list_by_uid($uid) {
		$tjs = $this->_d->list_by_conds(array(
			'm_uid' => $uid
		));

		return $tjs;
	}

	public function list_by_status($status, $uid) {
		return $this->_d->list_by_status($status, $uid);
	}

	public function list_uids_by_paperid($paperid) {
		$tjs = $this->_d->list_by_conds(array(
			'paper_id' => $paperid
		));

		$uids = array();
		foreach ($tjs as $tj) {
			$uids[] = $tj['m_uid'];
		}

		return $uids;
	}
	/**
     * 更新试卷到开始考试
     * @param $id 试卷id
     */
	public function update_to_begin($id,$status) {
		$data = array('status' => $status, 'my_begin_time'=> NOW_TIME);
		$conds = array('id' => $id);
		return $this->_d->update_by_conds($conds, $data);
	}
	/**
     * 更新试卷随机试题
     * @param $id 试卷id
     */
	public function update_random_tids($id,$random_tids) {
		$data = array('random_tids' => $random_tids);
		$conds = array('id' => $id);
		return $this->_d->update_by_conds($conds, $data);
	}
	/**
     * 获取试卷信息，关联paper表
     * @param $id 试卷id
	 * @return array
     */
	public function get_with_paper_by_id($id, $paper_id=0, $uid=0) {
		$paper = $this->_d->get_with_paper_by_id($id, $paper_id, $uid);
		if(!$paper){
			$paper_model = D("Exam/ExamPaper");
			$del_paper = $paper_model->get($paper_id);
			$paper['id'] = $del_paper['id'];
			$paper['status'] = 3;
		}

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

		$paper['members'] = array();
		if(!empty($paper['m_uids'])) {
			$conditions = array(
				'm_uid' => explode(',', $paper['m_uids']),
			);

			$members =  $this->_member_model->list_by_conds($conditions);
			foreach ($members as $value) {
				$paper['members'][] = $value['m_username'];
			}
		}

		if(!empty($paper['rules'])) {
			$paper['rules'] = unserialize($paper['rules']);
		}

		return $paper;
	}
	/**
     * 获取试卷信息
     * @param $id 试卷id
	 * @return array
     */
	public function get_by_id($id) {
		$paper = $this->_d->get_with_paper_by_id($id);
		if(!empty($paper['rules'])) {
			$paper['rules'] = unserialize($paper['rules']);
		}
		return $paper;
	}

	/**
     * 获取未开始试卷的用户id数组
     * @param $id 试卷id
	 * @return array
     */
	public function get_uids_by_notstart($paper_id) {
		$tjs = $this->_d->list_by_conds(array(
			'paper_id' => $paper_id,
			'status' => 0,
		));
		$uids = array();
		foreach ($tjs as $tj) {
			$uids[] = $tj['m_uid'];
		}
		return $uids;
	}

	/**
     * 获取考试结束、已开始考试、未交卷列表
     * @param $paper_id 试卷id
	 * @return array
     */
	public function list_by_notsubmit($paper_id) {
		return $this->_d->list_by_notsubmit($paper_id);
	}
	
}