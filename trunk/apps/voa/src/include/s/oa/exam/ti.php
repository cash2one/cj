<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_ti extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_ti();
		}
	}

	public function validator_type($type) {
		if (!isset(voa_d_oa_exam_ti::$TYPES[$type])) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::TYPE_ERROR, $type);
		}
		return true;
	}

	public function validator_orderby($orderby){
		if ($orderby < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::ORDERBY_ERROR, $orderby);
		}
		return true;
	}

	public function validator_score($score){
		if ($score < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::SCORE_ERROR, $score);
		}
		return true;
	}

	public function validator_tiku_id($tiku_id){
		if ($tiku_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::TIKU_ID_ERROR, $tiku_id);
		}
		return true;
	}

	public function validator_title($title) {
		$title = trim($title);
		if (!validator::is_required($title)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::TITLE_ERROR, $title);
		}

		return true;
	}

	public function validator_options($options) {
		$options = trim($options);
		if (!validator::is_required($options)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::OPTIONS_ERROR, $options);
		}

		return true;
	}

	public function validator_answer($answer) {
		$answer = trim($answer);
		if (!validator::is_required($answer)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::ANSWER_ERROR, $answer);
		}

		return true;
	}

	public function list_by_tiku_ids($ids) {
		return $this->list_by_conds(array('tiku_id' => $ids),null,array('tiku_id'=>'ASC'));
	}

	public function get_by_id($id) {
		return $this->get($id);
	}

	public function list_by_ids($ids) {
		return $this->list_by_pks($ids);
	}
	/**
	 * 获取实际存在的题目
	 * @param array $ids
	 * @return array $list
	 */
	public function list_by_ids_real($ids) {
		return $this->_d_class->list_by_ids_real($ids);
	}

}
