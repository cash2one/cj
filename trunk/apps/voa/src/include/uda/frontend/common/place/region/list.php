<?php
/**
 * list.php
 * 内部api方法/公共模块/场所管理/分区列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_region_list extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** place type service */
	private $__s_place_type = null;
	/** place region service */
	private $__s_place_region = null;

	public function __construct() {
		parent::__construct();

		if ($this->__s_place_region === null) {
			$this->__s_place_region = new voa_s_oa_common_place_region();
		}
		if ($this->__s_place_type === null) {
			$this->__s_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 列表指定条件的分区
	 * @param array $request 请求的字段
	 * + placetypeid 所在类型ID，如果parentid不为空，则该值允许为空，否则必须提供。
	 * + parentid 上级分区ID
	 * + childrens 是否递归显示所有下级。1=是，0=否，默认=0,不展示所有下级，只列出当前级下的
	 * + member 是否获取分区对应的负责人。1=是，0=否，默认=1，获取负责人信息
	 * @param array $result (引用结果)
	 * @param array $options 其他参数
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		/**
		 * 思路：
		 * 只显示某个类型下的分区
		 * 当未指定上级分区时，不指定类型将视为错误操作
		 */

		$this->__options = $options;

		// 字段规则定义
		$fields = array(
			'placetypeid' => array('placetypeid', parent::VAR_ABS, null, null, false),
			'parentid' => array('parentid', parent::VAR_ABS, null, null, false),
			'childrens' => array('childrens', parent::VAR_ABS, null, null, false),
			'member' => array('member', parent::VAR_INT, null, null, true)
		);
		// 检查字段值
		if (!$this->extract_field($this->__request, $fields, $request, true)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		if ($this->__request['placetypeid'] < 1 && $this->__request['parentid'] < 1) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::NO_ID);
		}

		// 所在类型信息，这个必须提供
		if ($this->__request['parentid'] > 0) {
			$place_type = $this->__s_place_type->get_place_type($this->__request['placetypeid'], true);
			if (!$place_type) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_LIST_TYPE_NOT_EXISTS
						, $this->__request['placetypeid']);
			}
		}

		// 指定的上级分区
		$parent_region = array();
		if ($this->__request['parentid'] > 0) {
			$parent_region = $this->__s_place_region->get_place_region($this->__request['parentid'], true, false);
			if (!$parent_region) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_LIST_PARENT_NOT_EXISTS
						, $this->__request['parentid']);
			}
		}

		// 读取全部分区数据
		$placeregionids = array();
		$list = $this->__s_place_region->get_place_region_list_all($this->__request['placetypeid']);
		!is_array($list) && $list = array();
		foreach ($list as $_region) {
			$_placeregionid = $_region['placeregionid'];
			$result['level'][$_region['parentid']][$_placeregionid] = $_placeregionid;
			$result['data'][$_placeregionid] = $_region;

			// 读取相关人员，则进行数据初始化，以及找到分区id
			if (!empty($this->__request['member'])) {
				$result['member'][$_placeregionid] = array(
					voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
					voa_d_oa_common_place_member::LEVEL_NORMAL => array()
				);
				$placeregionids[] = $_placeregionid;
			}

			unset($_placeregionid);
		}

		// 读取全部分区的相关人员
		if (!empty($this->__request['member']) && !empty($list)) {
			$s_place_member = new voa_s_oa_common_place_member();
			foreach ($s_place_member->get_placeregion_uid_list($placeregionids, 'all') as $_placeregionid => $_data) {
				foreach ($_data as $_level => $_m) {
					$result['member'][$_placeregionid][$_level] = array_keys($_m);
				}
				unset($_level, $_m);
			}
			unset($_placeregionid, $_data);
		}

		return true;
	}

}
