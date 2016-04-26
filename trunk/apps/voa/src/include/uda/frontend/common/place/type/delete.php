<?php
/**
 * delete.php
 * 内部api方法/公共模块/场所管理/类型删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_type_delete extends voa_uda_frontend_common_place_abstract {

	/** 请求的参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** 类型service类 */
	private $__service_place_type = null;

	public function __construct() {
		parent::__construct();

		if ($this->__service_place_type === null) {
			$this->__service_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 删除指定ID的类型
	 * @param array $request 请求的参数
	 * + placetypeid int 待删除的ID
	 * @param array $result (引用结果)结果
	 * + placetypeid int 已删除了的ID
	 * @param array $options 其他扩展参数
	 * + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__request = $request;
		// 参数请求规则
		$fields = array(
			'placetypeid' => array(
				'placetypeid', parent::VAR_INT,
				array($this->__service_place_type, 'validator_placetypeid'),
				null, false,
			),
		);
		// 参数可用性验证
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 如果类型下的分区不为空，则禁止删除
		$s_place_region = new voa_s_oa_common_place_region();
		if ($s_place_region->get_place_region_count_by_placetypeid($this->__request['placetypeid']) > 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::TYPE_DELETE_REGION_NOT_EMPTY);
		}

		// 删除类型数据
		$this->__service_place_type->delete($this->__request['placetypeid']);
		// 更新缓存
		if (empty($this->__options['no_update_cache'])) {
			$this->__service_place_type->get_place_type_cache(true);
		}
		// 返回结果
		$result = array();
		$result['placetypeid'] = $this->__request['placetypeid'];

		return true;
	}

}
