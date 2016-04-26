<?php
/**
 * add.php
 * 内部api方法/超级报表/添加报表模板
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_addtemplate extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();

	/** 类型 diy 类 */
	private $__diy = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_column_add();
			$this->_init_diy_data($this->__diy);
		}
	}

	/**
	 * 添加报表模板
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)
	 * @return boolean
	 */
	public function add_template(array $request, array &$result) {

		if (empty($request)) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_TEMPLATE_ERROR);
		}

		$templates = array();
		$k = 1;
		foreach ($request as $item) {
			$template = array();
			$template = array(
				'field' => 'field'.$k,
				'fieldname' => $item['fieldname'],
				'unit' => $item['unit'],
				'required' => $item['required'],
				'ct_type' => $item['ct_type'],
				'orderid' => $k
			);
			// 写入模板
			$result = array();
			$this->__diy->execute($templates, $result);
			$k++;
		}

		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::ADD_TEMPLATE_ERROR);
		}

		return true;
	}

}
