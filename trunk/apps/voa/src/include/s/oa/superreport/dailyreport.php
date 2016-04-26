<?php
/**
 * dailyreport.php
 * service/超级报表/日报
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_dailyreport extends voa_s_oa_superreport_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化日报数据
	 * @param array $list 日报列表
	 * @param int $date 日期
	 * @return array
	 */
	public function format_daily($templates, $daily, $fordward_daily = array()) {

		$result = array();
		foreach ($templates as $k => $template) {
			$field = '_'.$template['tc_id'];
			//当日
			$current = isset($daily[$field]) ? $daily[$field] : 0;
			//上一日
			$fordward = isset($fordward_daily[$field]) ? $fordward_daily[$field] : 0;
			//差额
			$gap = $current - $fordward;
			$result[$field]['field'] = $template['field'];
			$result[$field]['fieldname'] = $template['fieldname'];
			$result[$field]['unit'] = $template['unit'];
			$result[$field]['type'] = $template['ct_type'];
			$result[$field]['sort'] = $template['orderid'];
			if ($template['ct_type'] == 'int') {
				$result[$field]['current'] = $current;
				$result[$field]['forward'] = $fordward;
				$result[$field]['gap'] = $gap;

			} else {
				$current = isset($daily[$field]) ? $daily[$field] : '';
				$result[$field]['current'] = $current;
			}
		}

		return array_values($result);
	}

	/**
	 * 验证用户ID的基本合法性
	 * @param number $uid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_report($report) {

		if (!is_array($report)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DAILYREPORT_FORMAT_ERROR, $report);
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

	/**
	 * 验证评论每页显示数量的基本合法性
	 * @param number $limit
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_comment_limit($limit) {

		if ($limit < 1) {  //验证是否合法

			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::LIMIT_ERROR, $limit);
		}


		return true;
	}

}
