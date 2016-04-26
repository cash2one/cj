<?php
/**
 * add.php
 * 内部api方法/公共模块/场所管理/分区添加
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_region_add extends voa_uda_frontend_common_place_abstract {

	/** 外部请求的参数 */
	private $__request = array();
	/** 返回结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** region service对象 */
	private $__service_place_region = null;
	/** type service对象 */
	private $__service_place_type = null;
	/** member service */
	private $__service_place_member = null;

	public function __construct() {
		parent::__construct();

		if ($this->__service_place_region === null) {
			$this->__service_place_region = new voa_s_oa_common_place_region();
			$this->__service_place_type = new voa_s_oa_common_place_type();
			$this->__service_place_member = new voa_s_oa_common_place_member();
		}
	}

	/**
	 * 新增分区
	 * @param array $request
	 * + placetypeid 类型ID
	 * + parentid 上级分区ID
	 * + name 分区名称
	 * @param array $result
	 * @param array $options
	 * + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 自定义参数规则
		$fields = array(
			'placetypeid' => array(
				'placetypeid', parent::VAR_INT,
				array($this->__service_place_type, 'validator_placetypeid'),
				null, false
			),
			'parentid' => array(
				'parentid', parent::VAR_INT,
				null,
				null, false
			),
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__service_place_region, 'validator_place_region_name'),
				null, false
			)
		);
		// 字段参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 检查上级分区ID
		if ($this->__request['parentid'] < 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_PARENTID_NOT_EXISTS, $this->__request['parentid']);
		}

		// 检查类型是否存在
		if (!$this->__service_place_type->get_place_type($this->__request['placetypeid'], true)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_TYPE_NOT_EXISTS, $this->__request['placetypeid']);
		}

		// 获取新分区的级别深度值
		$deepin = 1;
		if (!$this->__get_deepin($deepin)) {
			return false;
		}

		// 检查分区名称是否重复
		if (!$this->__service_place_region->validator_place_region_name_duplicate($this->__request['placetypeid']
				, $this->__request['parentid'], $this->__request['name'])) {
			return false;
		}

		// 增加分区
		$data = array(
			'placetypeid' => $this->__request['placetypeid'],
			'parentid' => $this->__request['parentid'],
			'deepin' => $deepin,
			'name' => $this->__request['name']
		);

		// 写入数据
		$result = $this->__service_place_region->insert($data);

		// 更新缓存
		if (empty($this->__options['no_update_cache'])) {
			//$this->__service_place_region->get_place_region_cache(true);
		}

		return true;
	}

	/**
	 * 获取新增的分区的级别深度值
	 * @param number $new_region_deepin (引用结果)当前添加的分区级别深度
	 * @throws Exception
	 * @return boolean
	 */
	private function __get_deepin(&$new_region_deepin) {

		/**
		 * 思路：
		 * 用于标记分区所在的级别深度，利于后续判断
		 * 每增加一个级别，深度值+1，顶级分区深度值=1
		 * setting 定义最大允许的深度值，不允许超出该值（限制最大深度）
		 */

		// 未指定上级分区，也未指定类型，则出错
		if ($this->__request['parentid'] <= 0 && $this->__request['placetypeid'] <= 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_PARENT_ERROR);
		}

		// 新增的是顶级分区，直接输出
		if (!$this->__request['parentid']) {

			// 上级分区ID为：0
			$this->__request['parentid'] = 0;
			// 顶级分区级别深度为：1
			$new_region_deepin = 1;

			return true;
		}

		// 添加的是非顶级分区，做进一步的验证和判断

		// 获取上级分区信息
		$parent = $this->__service_place_region->get_place_region($this->__request['parentid'], true);
		// 不存在
		if (!$parent) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_PARENTID_NOT_EXISTS, $this->__request['parentid']);
		}

		// 检查类型是否对应
		if ($parent['placetypeid'] != $this->__request['placetypeid']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_PARENT_TYPE_ERROR
					, $this->__request['parentid'], $this->__request['placetypeid']);
		}

		// 检查上级分区是否已达到最大级数
		if ($parent['deepin'] >= $this->region_max_deepin) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ADD_DEEPIN_TOO_DEEP);
		}

		// 级别深度+1
		$new_region_deepin = $parent['deepin'] + 1;

		return true;
	}

}
