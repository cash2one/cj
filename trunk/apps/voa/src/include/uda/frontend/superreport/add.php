<?php
/**
 * add.php
 * 内部api方法/超级报表添加
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_add extends voa_uda_frontend_superreport_abstract {

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
			$this->__diy = new voa_uda_frontend_diy_data_add();
		}
	}

	/**
	 * 新增一个日报
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的日报信息数组
	 * @return boolean
	 */
	public function add_superreport($request, &$result) {

		// 定义参数请求规则
		$fields = array(
			// 门店ID
			'csp_id' => array(
				'csp_id', parent::VAR_INT,
				array($this->__service, 'validator_csp_id'),
				null, false,
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$csp_id =  $request['csp_id'];
		unset($request['csp_id']);

		$this->_init_diy_data($this->__diy);  //设置选项
		$service_detail = new voa_s_oa_superreport_detail();
		$service_month = new voa_s_oa_superreport_monthlyreport();

		try {

			$service_detail->begin();

			// 写入报表
			$result = array();
			$this->__diy->execute($request, $result);

			/** 写入报表详情 */
			$date = rgmdate(time(), 'Y-m-d');
			$result['date'] = $date;
			$result['csp_id'] = $csp_id;
			$detail = array(
				'm_uid' => $this->member['m_uid'],  //用户ID
				'dr_id' => $result['dr_id'],		//报表ID
				'csp_id' => $csp_id,				//门店ID
				'cdate' => $date
			);
			$service_detail->insert($detail);
			/** 写入报表详情end */

			/** 写入月报  */
			$year = date('Y',rstrtotime($date));
			$month = date('m',rstrtotime($date));
			if (0 > $year || 0 > $month) {
				return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DATE_ERROR);
			}
			$months = $service_month->get_month_data($year, $month, $csp_id);
			$tablecols = $this->tablecol;

			if ($months) { //如果已存在月报数据，则将本日数据加入本月数据

				$month_list = array();
				$updates = array();
				//取回本月数据
				foreach ($months as $v) {
					$month_list[$v['fieldname']] = $v['fieldvalue'];
				}

				foreach ($tablecols as $col) {
					if ($col['ct_type'] == 'int') { //如果是int型字段，则将本日数据加入本月数据中
						$field = '_'.$col['tc_id'];
						$month_data = isset($month_list[$field]) ? (float)$month_list[$field] : 0;
						$updates[$field] = $month_data + (float)$request[$field];
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

			} else { //如果还没有月报数据，则新建一条数据

				$inserts = array();
				foreach ($tablecols as $col) {
					if ($col['ct_type'] == 'int') { //如果是int型字段，则将本日数据加入
						$field = '_'.$col['tc_id'];
						$inserts[$field] =  $request[$field];
					}
				}

				if (!empty($inserts)) {
					//插入本月统计数据
					foreach ($inserts as $ki => $insert) {
						$insert_data[] = array(
							'csp_id' => $csp_id,
							'year' => $year,
							'month' => $month,
							'fieldname' => $ki,
							'fieldvalue' => $insert
						);
					}
					$service_month->insert_multi($insert_data);
				}
			}
			/** 写入月报 end */

			$service_detail->commit();

		} catch (Exception $e) {
			$service_detail->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

}
