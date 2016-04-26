<?php
/**
 * get.php
 * 根据指定条件精确获取一个区域的信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_region_get extends voa_uda_frontend_common_place_abstract {

	/** 外部请求的参数 */
	private $__request = array();
	/** 返回结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();

	/** region service对象 */
	private $__service_place_region = null;

	public function __construct() {
		parent::__construct();

		if ($this->__service_place_region === null) {
			$this->__service_place_region = new voa_s_oa_common_place_region();
			$this->__service_place_type = new voa_s_oa_common_place_type();
		}
	}

	/**
	 * 根据条件获取一个区域的信息（精准获取而非查询）
	 * @param array $request
	 * + placeregionid 指定分区id 如果指定了此参数，则下面几个参数均会被忽略
	 * + placetypeid 分区类型，此处查询必须提供
	 * + parentid 上级分区id，此处查询必须提供
	 * + name 分区名称，此处查询必须提供
	 * @param array $result
	 * @param array $options
	 * + from_db 是否强制从数据库读取，1=是，0=否，默认：0=否，从缓存读取
	 * + remove 是否读取已删除的，1=是，0=否，默认：0=否，不读取已删除的
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 自定义参数规则
		$fields = array(
			'placeregionid' => array('placeregionid', parent::VAR_INT, array(), null, true),
			'placetypeid' => array('placetypeid', parent::VAR_INT, array(), null, true),
			'parentid' => array('parentid', parent::VAR_INT, array(), null, true),
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__service_place_region, 'validator_place_region_name'),
				null, true
			)
		);
		// 字段参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new service_exception($this->errmsg, $this->errcode);
			return false;
		}

		$from_db = !empty($this->__options['from_db']) ? true : false;
		$remove = !empty($this->__options['remove']) ? true : false;

		$s_place_region = new voa_s_oa_common_place_region();

		// 指定了分区ID，则直接获取分区信息
		if (!empty($this->__request['placeregionid'])) {
			$result = $s_place_region->get_place_region($this->__request['placeregionid'], $from_db, $remove);
			if (!$result) {
				$result = false;
				return false;
			}
			$this->__out_result($result);
			return true;
		}

		if (!isset($this->__request['placetypeid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::LOSE_REGION_GET_TYPEID);
		}
		if (!isset($this->__request['parentid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::LOSE_REGION_GET_PARENTID);
		}
		if (!isset($this->__request['name'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::LOSE_REGION_GET_NAME);
		}

		// 检查场所类型是否存在
		$s_place_type = new voa_s_oa_common_place_type();
		if (!$s_place_type->get_place_type($this->__request['placetypeid'])) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::TYPE_NOT_EXISTS, $this->__request['placetypeid']);
		}

		// 检查上级分区是否存在
		if ($this->__request['parentid'] != 0
				&& !$s_place_region->get_place_region($this->__request['parentid'], $from_db, $remove)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PARENTID_NOT_EXISTS, $this->__request['parentid']);
		}

		// 找到对应的分区
		$result = $s_place_region->get_region_by_conds($this->__request['placetypeid']
				, $this->__request['parentid'], $this->__request['name']);
		if (!$result) {
			$result = false;
			return false;
		}
		$this->__out_result($result);

		return true;
	}

	/**
	 * 获取当前区域相关人员列表
	 * @return array
	 */
	private function __get_placeregion_member() {

		$s_place_member = new voa_s_oa_common_place_member();

		return (array)$s_place_member->get_place_region_member_list($this->__request['placeregionid'], 'all');
	}

	/**
	 * 输出结果
	 * @param array $result (引用结果)
	 * @return boolean
	 */
	private function __out_result(&$result) {

		if (!$result) {
			$result = array();
			return false;
		}

		$result['member_list'] = $this->__get_placeregion_member();

		return true;
	}

}
