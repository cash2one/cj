<?php
/**
 * delete.php
 * 内部api方法/公共模块/场所管理/删除场所
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_delete extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** service place */
	private $__s_place = null;
	/** service place member */
	private $__s_place_member = null;

	public function __construct() {
		parent::__construct();
		if ($this->__s_place === null) {
			$this->__s_place = new voa_s_oa_common_place();
			$this->__s_place_member = new voa_s_oa_common_place_member();
		}
	}

	/**
	 * 删除指定的场所
	 * @param array $request 请求的字段
	 * + placeid 待删除的场所id列表 array(1, 2, ...)
	 * @param array $result (引用结果)
	 * + array(1, 2, 3, ...) 已删除的场所id
	 * @param array $options 其他参数
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__request = $request;
		$this->__result = $result;
		$this->__options = $options;

		if (!isset($this->__request['placeid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_DELETE_UNKNOW);
		}
		if (!is_array($this->__request['placeid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_DELETE_FORMAT_ERROR);
		}

		// 整理待删除的id
		$deleted = array();
		foreach ($this->__request['placeid'] as $_id) {
			if (!is_numeric($_id)) {
				continue;
			}
			if (isset($deleted[$_id])) {
				continue;
			}
			$deleted[$_id] = $_id;
		}

		// 待删除的场所为空
		if (empty($deleted)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_DELETE_NULL);
		}

		// 删除场所
		$this->__s_place->delete_by_placeid($deleted);

		// 删除相关所有人员
		$this->__s_place_member->delete_place_member($deleted);

		// 返回已删除的场所id
		$result = $deleted;

		return true;
	}

}
