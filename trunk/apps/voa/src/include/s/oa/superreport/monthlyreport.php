<?php
/**
 * monthlyreport.php
 * service/超级报表/月报
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_monthlyreport extends voa_s_oa_superreport_abstract {

	public function __construct() {
		parent::__construct();
		$this->_d_class = new voa_d_oa_superreport_monthlyreport();
	}

	/**
	 * 根据门店ID读取本月月报和上月月报
	 * @param number $s_id 报表ID
	 * @param number $year 年份
	 * * @param number $month 月份
	 * @return array
	 */
	public function get_monthreport($csp_id, $year, $month) {

		$current = $this->_d_class->get_by_conds(array('csp_id' => $csp_id, 'year' => $year, 'month' => $month));
		if (!$current) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::MONTHLYREPORT_ERROR);
		}
		$forward_month = date('n', mktime(0, 0, 0, $month-1, 1, $year)); //取得上月月份
		$months = array($month, $forward_month);
		$list = $this->_d_class->list_by_conds(array('csp_id' => $csp_id, 'year' => $year, 'month' => $months));

		return $list;
	}

	/**
	 * 分离本月数据和上月数据
	 * @param array $list 月报列表
	 * @param int $month 本月月份
	 * @return array
	 */
	public  function separate_report($list, $month) {

		$current = array();
		$forward = array();
		if ($list) {
			foreach ($list as $k => $v) {
				if ($v['month'] == $month) { //本月数据
					$current[$v['fieldname']] = $v['fieldvalue'];
				}
				if ($v['month'] == $month - 1) {  //上月数据
					$forward[$v['fieldname']] = $v['fieldvalue'];
				}
			}
		}

		return array($current, $forward);
	}

	/**
	 * 格式化月报数据
	 * @param array $templates 模板列表
	 * @param array $month 本月数据
	 * @param array $fordward_month 上月数据
	 * @return array
	 */
	public function format_month($templates, $month, $fordward_month = array()) {

		$result = array();
		foreach ($templates as $k => $template) {
			$field = '_'.$template['tc_id'];
			//当日
			$current = isset($month[$field]) ? $month[$field] : 0;
			//上一日
			$fordward = isset($fordward_month[$field]) ? $fordward_month[$field] : 0;
			//差额
			$gap = $current - $fordward;

			$result[$field]['field'] = $field;
			$result[$field]['fieldname'] = $template['fieldname'];
			$result[$field]['unit'] = $template['unit'];
			$result[$field]['type'] = $template['ct_type'];
			$result[$field]['sort'] = $template['orderid'];
			if ($template['ct_type'] == 'int') {
				$result[$field]['current'] = $current;
				$result[$field]['forward'] = $fordward;
				$result[$field]['gap'] = $gap;
			} else {
				$current = isset($month[$field]) ? $month[$field] : '';
				$result[$field]['current'] = $current;
			}
		}

		return array_values($result);
	}

	/**
	 * 根据门店ID和日期读取一条数据
	 * @param number $sp_id 门店ID
	 * @param string $year 年份
	 * @param int $month 月份
	 * @return array
	 */
	public function get_month_data($year, $month, $csp_id) {

		$result = $this->_d_class->list_by_conds(array('year' => $year, 'month' => $month, 'csp_id' => $csp_id));

		return $result;
	}

	/**
	 * 验证门店ID的基本合法性
	 * @param number $sp_id
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
	 * 验证月份的基本合法性
	 * @param number $month
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_month($month) {

		$month = (int)$month;
		if ($month < 1 || $month > 12) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::MONTH_ERROR, $month);
		}

		return true;
	}

	/**
	 * 验证年份的基本合法性
	 * @param number $year
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_year($year) {

		$year = (int)$year;
		if ($year < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::YEAR_ERROR, $year);
		}

		return true;
	}

	/**
	 * 验证月报字段值的基本合法性
	 * @param number $value
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_fieldvalue($value) {

		if (! validator::is_int($value) ) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::FIELDVALUE_ERROR, $value);
		}

		return true;
	}

}
