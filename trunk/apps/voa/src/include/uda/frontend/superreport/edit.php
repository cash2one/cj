<?php
/**
 * edit.php
 * 内部api方法/超级报表/报表编辑
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_edit extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 类型 service 类 */
	private $__service = null;
	/** diy uda 类 */
	private $__diy = null;


	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();

	if ($this->__service == null) {
			$this->__service = new voa_s_oa_superreport_dailyreport();
		}

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_data_update();
		}
	}

	/**
	 * 编辑日报
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的日报信息数组
	 * @return boolean
	 */
	public function edit_superreport($request, &$result) {

		// 定义参数请求规则
		$fields = array(
			// 请求参数 name（类型名称）
			'dr_id' => array(
				'dr_id', parent::VAR_INT,
				array($this->__service, 'validator_dr_id'),
				null, false,
			),
			'csp_id' => array(
				'csp_id', parent::VAR_INT,
				array($this->__service, 'validator_csp_id'),
				null, false,
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$csp_id = $this->__request['csp_id'];
		$dr_id = $this->__request['dr_id'];
		unset($request['csp_id']);

		//检查用户是否有更改权限（若现用户绑定的门店与日报记录的门店一致，则有权限）
		$dr_id = $this->__request['dr_id'];
		$s_daily_detail = new voa_s_oa_superreport_detail();
		$detail = $s_daily_detail->get_detail_by_dr_id($dr_id);
		if ($csp_id != $detail['csp_id']) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_RIGHT_ERROR);
		}

		//取回原有日报数据
		$diy_uda_data_get = new voa_uda_frontend_diy_data_get();
		$this->_init_diy_data($diy_uda_data_get);  //设置选项
		$daily = array();
		$diy_uda_data_get->execute(array('dr_id' => $dr_id), $daily);

		$this->_init_diy_data($this->__diy);  //设置选项
		$service_daily = new voa_s_oa_superreport_dailyreport();
		$service_month = new voa_s_oa_superreport_monthlyreport();

		try {

			$service_month->begin();

			// 更新报表
			$this->__diy->execute($request, $result);

			/** 更新月报  */
			$date = $detail['cdate'];
			$year = date('Y',rstrtotime($date));
			$month = date('m',rstrtotime($date));
			$result['date'] = $date;
			$result['csp_id'] = $csp_id;
			if (0 > $year || 0 > $month) {
				return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DATE_ERROR);
			}
			//取回月报数据
			$months = $service_month->get_month_data($year, $month, $csp_id);

			if ($months) { //如果已存在月报数据，则将    月报数据-原日报数据+新日报数据

				$month_list = array();
				$updates = array();

				//取回本月数据
				foreach ($months as $v) {
					$month_list[$v['fieldname']] = $v['fieldvalue'];
				}
				$tablecols = $this->tablecol;
				foreach ($tablecols as $col) {
					if ($col['ct_type'] == 'int') { //如果是int型字段，则将新的本日数据加入本月数据中
						$field = '_'.$col['tc_id'];
						$month_data = isset($month_list[$field]) ? (float)$month_list[$field] : 0;
						$updates[$field] = $month_data - $daily[$field] + $request[$field];
					}
				}
				if (!empty($updates)) {
					//更新本月统计数据
					foreach ($updates as $k => $update) {
						$conds = array(
							'csp_id' => $csp_id,
							'year' => $year,
							'month' => $month,
							'fieldname' => $k
						);
						$service_month->update_by_conds($conds, array('fieldvalue' => $update));
					}
				}

			} else {

				return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_MONTH_ERROR);
			}
			/** 更新月报 end */

			$service_month->commit();

		} catch (Exception $e) {

			$service_month->rollback();

			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

}
