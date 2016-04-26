<?php
/**
 * detail.php
 * service/超级报表/日报相关数据
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_detail extends voa_s_oa_superreport_abstract {

	public function __construct() {
		parent::__construct();
		$this->_d_class = new voa_d_oa_superreport_detail();
	}

	/**
	 * 根据日报ID读取日报详情
	 * @param number $dr_id 日报ID
	 * @return array
	 */
	public function get_detail_by_dr_id($dr_id) {

		$list = $this->_d_class->get_by_conds(array('dr_id' => $dr_id));

		return $list;
	}

	/**
	 * 格式化日报数据
	 * @param array $list 日报列表
	 * @param int $date 日期
	 * @return array
	 */
	protected  function format_dailyreport($list, $date) {

		$result = array();
		if ($list) {
			foreach ($list as $k => $v) {
				$tmp = 0;
				$current = 0;
				$forward = 0;
				if ($v['month'] == $month) { //本月数据
					$current = $v['fieldvalue'];
					$result[$v['fieldname']]['current'] = $current;
				}
				if ($v['month'] == $month - 1) {  //上月数据
					$forward = $v['fieldvalue'];
					$result[$v['fieldname']]['forward'] = $forward;
				}
				$tmp = $current - $forward;   //差额
				$result[$v['fieldname']]['gap'] = $tmp;
			}
		}

		return $result;
	}

	/**
	 * 验证用户ID的基本合法性
	 * @param number $uid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_uid($uid) {

		if ($uid < 1) {  //验证是否合法

			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::UID_ERROR, $uid);
		}

		return true;
	}

	/**
	 * 验证月份的基本合法性
	 * @param number $month
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_date($date) {

		if (!validator::is_date($date)) {  //验证是否合法

			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DATE_ERROR, $date);
		}

		return true;
	}

	/**
	 * 验证日报ID的基本合法性
	 * @param number $month
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_dr_id($dr_id) {

		if ($dr_id < 1) {  //验证是否合法

			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::SID_ERROR, $dr_id);
		}


		return true;
	}

	/**
	 * 验证门店ID的基本合法性
	 * @param number $month
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_csp_id($csp_id) {

		if ($csp_id < 1) {  //验证是否合法

			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::SPID_ERROR, $csp_id);
		}


		return true;
	}

}
