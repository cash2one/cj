<?php
/**
 * list.php
 * 内部api方法/超级报表---模板列表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_listtemplates extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** diy uda 类 */
	private $__diy = null;
	/** service 类 */
	private $__service = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_template();
		}
	}
	/**
	 * 取回所有模板
	 * @param array $result 返回结果（引用）
	 */
	public function result(&$result) {

		$result =  $this->__service->list_all_templates();

		return true;
	}

}
