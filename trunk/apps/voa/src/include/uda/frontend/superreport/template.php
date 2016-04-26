<?php
/**
 * template.php
 * 内部api方法/超级报表模板
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_template extends voa_uda_frontend_superreport_abstract {

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

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_column_list();
		}
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_superreport_template();
		}
	}

	/**
	 * 取得模板
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)月报信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function get_template(array $request, array &$result) {

		$s_detail = new voa_s_oa_superreport_detail();
		// 定义参数请求规则
		$fields = array(
			// 请求参数 门店ID
			'csp_id' => array(
				'csp_id', parent::VAR_INT,
				array($s_detail, 'validator_csp_id'),
				null, false,
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}


		// 取得查询参数
		$csp_id =  $this->__request['csp_id'];
		//判断今日报表是否已发送
		$date = rgmdate(time(),'Y-m-d');
		$detail = $s_detail->get_by_conds(array('csp_id' => $csp_id, 'cdate' => $date));
		if ($detail) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DUMPLICATE_ERROR);
		}
		// 取得模板数据
		$list = array();
		$this->_init_diy_data($this->__diy);  //设置选项
		$this->__diy->execute(array(),$list);
		if (!$list) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_TEMPLATE_ERROR);
		}
		$result = $this->__service->format_template($list);

		return true;
	}

	/**
	 * 取得模板原始数据
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)月报信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function get_raw_template(array $request, array &$result) {


		// 取得模板数据
		$this->_init_diy_data($this->__diy);  //设置选项
		$this->__diy->execute(array(),$result);
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_TEMPLATE_ERROR);
		}

		return true;
	}


}
