<?php
/**
 * delete.php
 * 内部api方法/公共模块/场所管理/分区删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_region_delete extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** place service */
	private $__s_place = null;
	/** place region service */
	private $__s_place_region = null;
	/** place type service */
	private $__s_place_type = null;

	public function __construct() {
		parent::__construct();

		if ($this->__s_place_region === null) {
			$this->__s_place_region = new voa_s_oa_common_place_region();
			$this->__s_place = new voa_s_oa_common_place();
			$this->__s_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 删除分区
	 * @param array $request 请求的字段
	 * + placeregionid array 待删除的分区ID
	 * @param array $result (引用结果)
	 * @param array $options 其他参数
	 * + no_update_cache 是否不更新缓存 true=不更新，false=更新，默认：更新
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		/**
		 * 思路：
		 * 不允许删除存在下级分区的分区 —— 必须删除所有下级
		 * 不允许删除存在下级场所的分区 —— 必须删除所有场所
		 */

		$this->__options = $options;

		// 自定义参数规则
		$fields = array(
			'placeregionid' => array(
				'placeregionid', parent::VAR_ARR, array(), null, false),
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 统计待删除的区域下的分区数
		$children_count_list = $this->__s_place_region->count_children_by_placeregionids($this->__request['placeregionid']);
		// 遍历以查找下级非空的
		foreach ($children_count_list as $_id => $_count) {
			if ($_count > 0) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_DELETE_HAVE_SUBREGION, $_id);
			}
		}
		unset($_id, $_count);

		// 统计待删除的区域下的场所数
		$place_count_list = $this->__s_place->count_place_by_placeregionids($this->__request['placeregionid']);
		// 遍历以查找存在场所的分区
		foreach ($place_count_list as $_id => $_count) {
			if ($_count > 0) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_DELETE_HAVE_PLACE, $_id);
			}
		}

		// 删除
		$this->__s_place_region->remove($this->__request['placeregionid']);

		// 删除相关人员
		$s_place_member = new voa_s_oa_common_place_member();
		$s_place_member->delete_place_region_member($this->__request['placeregionid']);

		// 更新缓存
		$this->__s_place_region->get_place_region_cache(true);

		$result = $this->__request['placeregionid'];

		return true;
	}

}
