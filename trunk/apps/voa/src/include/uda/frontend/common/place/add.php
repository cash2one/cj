<?php
/**
 * add.php
 * 内部api方法/公共模块/场所管理/新增场所
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_add extends voa_uda_frontend_common_place_abstract {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** service place */
	private $__s_place = null;
	/** service place type */
	private $__s_place_type = null;
	/** service place region */
	private $__s_place_region = null;
	/** service place member */
	private $__s_place_member = null;

	public function __construct() {
		parent::__construct();

		if ($this->__s_place === null) {
			$this->__s_place = new voa_s_oa_common_place();
			$this->__s_place_region = new voa_s_oa_common_place_region();
			$this->__s_place_type = new voa_s_oa_common_place_type();
			$this->__s_place_member = new voa_s_oa_common_place_member();
		}
	}

	/**
	 * 新增单个门店
	 * @param array $request 请求的字段
	 * + placetypeid 所在类型id
	 * + placeregionid 所在分区id
	 * + name 场地名称
	 * + address 场地地址
	 * + lng 经度
	 * + lat 纬度
	 * @param array $result (引用结果)
	 * + placeid 场所id
	 * + ... 场所的其他数据
	 * @param array $options 其他参数
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 字段规则定义
		$fields = array(
			'placetypeid' => array(
				'placetypeid', parent::VAR_INT, array($this->__s_place, 'validator_placetypeid'),
				null, false
			),
			'placeregionid' => array(
				'placeregionid', parent::VAR_INT,array(),
				null, false
			),
			'name' => array(
				'name', parent::VAR_STR, array($this->__s_place, 'validator_place_name'),
				null, false
			),
			'address' => array(
				'address', parent::VAR_STR, array($this->__s_place, 'validator_place_address'),
				null, false
			),
			'lng' => array(
				'lng', parent::VAR_STR, array($this->__s_place, 'validator_place_lnglat'),
				null, false
			),
			'lat' => array(
				'lat', parent::VAR_STR, array($this->__s_place, 'validator_place_lnglat'),
				null, false
			)
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		/** 进一步检查 */

		// 确定分区合法性
		if (!$this->__s_place->validator_placeregionid($this->__request['placetypeid'], $this->__request['placeregionid'])) {
			return false;
		}

		// 确定地点名称无重复
		if (!$this->__s_place->validator_place_name_address($this->__request['placeregionid']
				, $this->__request['name'], $this->__request['address'])) {
			return false;
		}

		$r = $this->__write_database();

		$result = $this->__result;

		return $r;
	}

	/**
	 * 写入数据库
	 * @return boolean
	 */
	private function __write_database() {

		// 新增地点数据
		$new = array(
			'placetypeid' => $this->__request['placetypeid'],
			'placeregionid' => $this->__request['placeregionid'],
			'name' => $this->__request['name'],
			'address' => $this->__request['address'],
			'lng' => $this->__request['lng'],
			'lat' => $this->__request['lat'],
			'remove' => voa_d_oa_common_place::REMOVE_NO
		);
		$new = $this->__s_place->insert($new);

		if (empty($new['placeid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_ADD_DB_ERROR);
		}

		$this->__result = $new;

		return true;
	}

}
