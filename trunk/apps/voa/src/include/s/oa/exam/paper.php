<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_paper extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_paper();
		}
	}

	public function validator_name($name) {
		$name = trim($name);
		if (!validator::is_required($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_NAME_ERROR, $name);
		}

		return true;
	}

	public function validator_type($type) {
		if (!is_numeric($type)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_TYPE_ERROR, $type);
		}
		return true;
	}

	public function validator_paper_time($time) {
		if (!is_numeric($time) || $time <= 0) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_TIME_ERROR, $time);
		}
		return true;
	}

	public function validator_tiku($tiku) {
		if(!is_array($tiku))
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_DETAIL_TI_ID_ERROR, $tiku);

		foreach ($tiku as $v) {
			if(!is_numeric($v)) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_TIKU_ID_ERROR, $tiku);
			}
		}
		return true;
	}

	public function validator_rules($rules) {
		
		foreach (voa_d_oa_exam_ti::$TYPES as $key => $v) {
			if(!isset($rules[$key])) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_RULES_ERROR, $rules);
			}

			$rule = $rules[$key];
			if(!is_numeric($rule['num']) || $rule['num'] < 0 || !is_numeric($rule['score']) || $rule['num'] < 0) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_RULES_ERROR, $rules);
			}
		}
		return true;
	}

	public function validator_cover_id($cover_id) {
		if (!is_numeric($cover_id)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_COVER_ID_ERROR, $cover_id);
		}
		return true;
	}

	public function validator_begin_time($time) {
		if(false === rstrtotime($time)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_BEGIN_TIME_ID_ERROR, $time);
		}

		return true;
	}

	public function validator_end_time($time) {
		if(false === rstrtotime($time)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_END_TIME_ID_ERROR, $time);
		}

		return true;
	}

	public function validator_intro($intro) {
		$intro = trim($intro);
		if (!validator::is_required($intro)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_INTRO_ERROR, $intro);
		}

		return true;
	}

	public function get_by_id($id) {
		$paper = $this->get($id);
		if($paper) {
			$paper['tiku'] = $paper['tiku'] == 0 ? 0 : explode(',', $paper['tiku']);
			$paper['rules'] = empty($paper['rules']) ? array() : unserialize($paper['rules']);
			$paper['begin_time'] = empty($paper['begin_time']) ? '' : rgmdate('Y-m-d H:i:s', $paper['begin_time']);
			$paper['end_time'] = empty($paper['end_time']) ? '' : rgmdate('Y-m-d H:i:s', $paper['end_time']);
			$paper['departments'] = array();
			if(!empty($paper['cd_ids'])) {
				$s_department = new voa_s_oa_common_department();
				$departments =  $s_department->fetch_all_by_cd_ids(explode(',', $paper['cd_ids']));
				foreach ($departments as $value) {
					$paper['departments'][] = $value['cd_name'];
				}
			}
		}

		return $paper;
	}
}
