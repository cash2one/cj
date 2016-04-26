<?php
/**
 * edit.php
 * 内部api方法/公共模块/场所管理/类型编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_type_edit extends voa_uda_frontend_common_place_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 传入的其他参数 */
	private $__options = array();

	/** 类型 service 类 */
	private $__service_place_type = null;
	/** 待编辑的类型旧数据 */
	private $__placetype = array();


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
	 * 编辑类型
	 * @param array $request 请求的参数
	 * + placetypeid 待编辑的类型ID
	 * + name 类型名称
	 * + levels 级别权限称谓定义
	 * @param array $result (引用结果)编辑后的类型数据数组
	 * @param array $options 其他扩展参数
	 * + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 定义参数请求规则
		$fields = array(
			// 待编辑的类型ID
			'placetypeid' => array(
				'placetypeid', parent::VAR_INT,
				array($this->__service_place_type, 'validator_placetypeid'),
				null, false,
			),
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
				null, true,
			),
		);
		// 字段参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 检查是否重名
		if (!$this->__service_place_type->validator_place_type_name_duplicate($this->__request['name'], $this->__request['placetypeid'])) {
			return false;
		}

		// 强制从数据库获取类型数据
		$this->__placetype = $this->__service_place_type->get_place_type($this->__request['placetypeid'], true);
		if (empty($this->__placetype)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_TYPE_EDIT_NOT_EXISTS, $this->__request['placetypeid']);
		}

		// 发生改变的数据
		$updated = array();
		// 提交的数据（新数据）
		$new_data = $this->__request;
		// 如果提交了自定义级别权限称谓
		if (isset($new_data['levels'])) {
			// 将称谓数组变序列化，以利于检查变更情况
			$new_data['levels'] = serialize($new_data['levels']);
		}
		// 找到发生改变的数据
		$this->updated_fields($this->__placetype, $new_data, $updated);
		unset($new_data);

		// 未发生改变直接返回
		if (empty($updated)) {
			$result = $this->__placetype;
			return true;
		}

		// 整理数据为数据表储存需要的类型
		$this->__service_place_type->reset_place_type($updated);
		// 更新数据
		$this->__service_place_type->update($this->__request['placetypeid'], $updated);
		// 更新类型缓存
		if (empty($this->__options['no_update_cache'])) {
			$this->__service_place_type->get_place_type_cache(true);
		}
		// 返回结果
		$result = array_merge($this->__placetype, $updated);

		return true;
	}

}
