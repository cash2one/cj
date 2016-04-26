<?php
/**
 * list.php
 * 内部api方法/公共模块/场所管理/类型列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_type_list extends voa_uda_frontend_common_place_abstract {

	/** 外部请求的参数 */
	private $__request = array();
	/** 返回结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** place type serive 对象 */
	private $__service_place_type = null;

	public function __construct() {
		parent::__construct();

		if ($this->__service_place_type === null) {
			$this->__service_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 列表类型
	 * @param array $request
	 * + from_db 是否强制自数据库读取，可选。1=强制从数据库读取，0=自缓存读取。默认：自缓存读取
	 * @param array $result
	 * @param array $options
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		// 请求参数规则
		$fields = array(
			'from_db' => array(
				'from_db', parent::VAR_INT, null, null, true
			)
		);
		// 参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 是否自数据库强制读取
		$from_db = false;
		if (!empty($this->__request['from_db'])) {
			$from_db = true;
		}

		// 返回结果
		$result = $this->__service_place_type->get_place_type_cache($from_db);

		return true;
	}

}
