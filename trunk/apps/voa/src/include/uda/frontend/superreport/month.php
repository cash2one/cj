<?php
/**
 * month.php
 * 内部api方法/超级报表查看月报
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_month extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** service 类 */
	private $__service = null;
	/** 其他扩展参数 */
	private $__options = array();

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_monthlyreport();
		}
	}

	/**
	 * 取得月报
	 * @param array $request 请求的参数
	 * + csp_id 门店ID
	 * + year 年份
	 * + month 月份
	 * @param array $result (引用结果)月报信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function get_monthreport(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			// 请求参数 门店ID
			'csp_id' => array(
				'csp_id', parent::VAR_INT,
				array($this->__service, 'validator_csp_id'),
				null, false,
			),
			// 年份
			'year' => array(
				'year', parent::VAR_INT,
				array($this->__service, 'validator_year'),
				null, false
			),
			// 月份
			'month' => array(
				'month', parent::VAR_INT,
				array($this->__service, 'validator_month'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 取得查询参数
		$csp_id =  $this->__request['csp_id'];
		$year =  $this->__request['year'];
		$month =  $this->__request['month'];

		//取得门店
		$shop = array();
		$uda_shop = &uda::factory('voa_uda_frontend_common_place_get');
		$uda_shop->doit(array('placeid' => $csp_id), $shop);
		$result['csp_name'] = $shop['place']['name'];

		// 取得月报数据
		$data = $this->__service->get_monthreport($csp_id, $year, $month);
		if (!$data) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::MONTHLYREPORT_ERROR);
		}

		// 取得模板数据
		$templates = array();
		$diy = new voa_uda_frontend_diy_column_list();
		$this->_init_diy_data($diy);  //设置选项
		$diy->execute(array(),$templates);

		list($current_month, $forward_month) = $this->__service->separate_report($data, $month);
		$result['report'] = $this->__service->format_month($templates, $current_month, $forward_month);

		return true;
	}

}
