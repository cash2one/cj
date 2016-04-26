<?php
/**
 * edit.php
 * 内部api方法/公共模块/场所管理/分区编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_region_edit extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** place region service */
	private $__s_place_region = null;
	/** place member service */
	private $__s_place_member = null;
	/** 当前待编辑的分区信息 */
	private $__place_region = array();

	public function __construct() {
		parent::__construct();

		if ($this->__s_place_region === null) {
			$this->__s_place_region = new voa_s_oa_common_place_region();
			$this->__s_place_member = new voa_s_oa_common_place_member();
		}
	}

	/**
	 * 编辑分区
	 * @param array $request 请求的字段
	 * + placeregionid 待更新的分区ID
	 * + name 分区名称
	 * @param array $result (引用结果)
	 * @param array $options 其他参数
	 * + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		/**
		 * 思路：
		 * 目前暂时只允许更改名称，不允许更改级别
		 * 后面迭代再考虑此功能
		 */

		$this->__options = $options;

		// 字段规则定义
		$fields = array(
			'placeregionid' => array(
				'placeregionid', parent::VAR_INT,
				array($this->__s_place_region, 'validator_placeregionid'),
				null, false
			),
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__s_place_region, 'validator_place_region_name'),
				null, false
			)
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 当前分区信息
		$this->__place_region = $this->__s_place_region->get_place_region($this->__request['placeregionid'], true);
		if (empty($this->__place_region)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_EDIT_NOT_EXISTS, $this->__request['placeregionid']);
		}

		// 找到发生变动的数据
		$changed = array();
		$this->updated_fields($this->__place_region, $this->__request, $changed);
		// 不存在变更，则直接返回
		if (empty($changed)) {
			$result = $this->__place_region;
			return true;
		}

		// 检查名称是否重复
		if (isset($changed['name']) && !$this->__s_place_region->validator_place_region_name_duplicate($this->__place_region['placetypeid']
				, $this->__place_region['parentid'], $changed['name'], $this->__request['placeregionid'])) {
			return false;
		}

		// 更新数据
		$this->__s_place_region->update($this->__request['placeregionid'], $changed);

		// 更新缓存
		if (empty($this->__options['no_update_cache'])) {
			$this->__s_place_region->get_place_region_cache(true);
		}

		$result = $this->__s_place_region->get($this->__request['placeregionid']);

		return true;
	}

}
