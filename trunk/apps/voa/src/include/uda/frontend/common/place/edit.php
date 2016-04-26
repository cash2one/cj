<?php
/**
 * edit.php
 * 内部api方法/公共模块/场所管理/编辑场所
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_edit extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** 当前场所信息 */
	private $__place = array();
	/** 当前场所所有相关人员（包含负责人）按权限分组列表 */
	private $__place_member_list = array();

	/** service place*/
	private $__s_place = null;
	/** service place member */
	private $__s_place_member = null;
	/** service place type */
	private $__s_place_type = null;
	/** service place region */
	private $__s_place_region = null;

	/** 发生变动的字段数据 */
	private $__updated = array();
	/** 发生变动的关联人员 */
	private $__updated_member = array();

	public function __construct() {
		parent::__construct();

		if ($this->__s_place === null) {
			$this->__s_place = new voa_s_oa_common_place();
			$this->__s_place_member = new voa_s_oa_common_place_member();
			$this->__s_place_region = new voa_s_oa_common_place_region();
			$this->__s_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 编辑门店信息
	 * @param array $request 请求的字段
	 * + placeid 待编辑的门店ID，必须
	 * + placeregionid 所在区域ID，可选
	 * + name 门店名称，可选
	 * + address 门店地址，可选
	 * + lng 经度，可选
	 * + lat 纬度 ，可选
	 * @param array $result (引用结果)
	 * @param array $options 其他参数
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 字段规则定义
		$fields = array(
			'placeid' => array(
				'placeid', parent::VAR_INT, array($this->__s_place, 'validator_placeid'),
				null, false
			),
			'placeregionid' => array(
				'placeregionid', parent::VAR_INT, array(),
				null, true
			),
			'name' => array(
				'name', parent::VAR_STR, array($this->__s_place, 'validator_place_name'),
				null, true
			),
			'address' => array(
				'address', parent::VAR_STR, array($this->__s_place, 'validator_place_address'),
				null, true
			),
			'lng' => array(
				'lng', parent::VAR_STR, array($this->__s_place, 'validator_place_lnglat'),
				null, true
			),
			'lat' => array(
				'lat', parent::VAR_STR, array($this->__s_place, 'validator_place_lnglat'),
				null, true
			)
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 当前场所信息
		$this->__place = $this->__s_place->get_place_by_placeid($this->__request['placeid'], true);
		if (empty($this->__place)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_EDIT_NOT_EXISTS, $this->__request['placeid']);
		}

		// 分析发生改变的场所数据
		if (!$this->updated_fields($this->__place, $this->__request, $this->__updated)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PARASE_UPDATED_ERROR);
		}

		// 场所信息未发生变动，直接返回
		if (!$this->__updated) {
			$result = $this->__place;
			return true;
		}

		// 验证分区合法性
		if (isset($this->__updated['placeregionid']) && !$this->__validator_placeregionid()) {
			return false;
		}

		// 验证地点名称合法性
		if ((isset($this->__updated['name']) || isset($this->__updated['address'])) && !$this->__validator_place_name_address()) {
			return false;
		}

		// 更新数据
		$this->__s_place->update($this->__request['placeid'], $this->__updated);

		$result = $this->__s_place->get($this->__request['placeid']);

		return true;
	}

	/**
	 * 验证分区合法性
	 * @return boolean
	 */
	private function __validator_placeregionid() {

		// 场所类型不可更改
		$placetypeid = $this->__place['placetypeid'];
		// 未发生变动，则忽略检查
		if (!isset($this->__updated['placeregionid'])) {
			return true;
		}

		// 确定分区合法性
		if (!$this->__s_place->validator_placeregionid($placetypeid, $this->__updated['placeregionid'])) {
			return false;
		}

		return true;
	}

	/**
	 * 验证地点名称和地址合法性
	 * @return boolean
	 */
	private function __validator_place_name_address() {

		// 当前待编辑的场地所在分区
		$placeregionid = isset($this->__updated['placeregionid'])
				? $this->__updated['placeregionid'] : $this->__place['placeregionid'];
		// 名称
		$name = isset($this->__updated['name'])
				? $this->__updated['name'] : $this->__place['name'];
		// 地址
		$address = isset($this->__updated['address'])
				? $this->__updated['address'] : $this->__place['address'];

		// 确定地点名称无重复
		if (!$this->__s_place->validator_place_name_address($placeregionid, $name, $address, $this->__place['placeid'])) {
			return false;
		}

		return true;
	}

}
