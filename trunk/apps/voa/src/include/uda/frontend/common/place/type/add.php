<?php
/**
 * type.php
 * 内部api方法/公共模块/场所管理/类型添加
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_type_add extends voa_uda_frontend_common_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他扩展参数 */
	private $__options = array();

	/** 类型 service 类 */
	private $__service_place_type = null;

	/**
	 * 初始化
	 * 引入 place_type service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service_place_type === null) {
			$this->__service_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 新增一个场所类型
	 * @param array $request 请求的参数
	 * + name 类型名称
	 * + levels 级别权限称谓定义
	 * @param array $result (引用结果)新增的场所信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * + + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 定义参数请求规则
		$fields = array(
			// 请求参数 name（类型名称）
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__service_place_type, 'validator_place_type_name'),
				null, false,
			),
			// 请求类型级别权限称谓
			'levels' => array(
				'levels', parent::VAR_ARR,
				array($this->__service_place_type, 'validator_place_type_level_names'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 检查名字是否重复
		if (!$this->__service_place_type->validator_place_type_name_duplicate($this->__request['name'])) {
			return false;
		}

		// 检查类型总数是否超出系统限制
		if (!$this->__service_place_type->validator_place_type_amount()) {
			return false;
		}

		// 写入数据表的类型数据
		$new_type = array(
			'name' => $this->__request['name'],
			'levels' => $this->__request['levels']
		);

		// 转换数据为数据表可储存类型
		$this->__service_place_type->reset_place_type($new_type);
		// 写入数据，并返回新类型
		$result = $this->__service_place_type->insert($new_type);
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_TYPE_ADD_DB_ERROR);
		}
		// 更新类型缓存
		if (empty($this->__options['no_update_cache'])) {
			$this->__service_place_type->get_place_type_cache(true);
		}

		return true;
	}

}
