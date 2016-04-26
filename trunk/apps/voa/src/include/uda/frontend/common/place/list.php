<?php
/**
 * list.php
 * 内部api方法/公共模块/场所管理/场所列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_list extends voa_uda_frontend_common_place_abstract {

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
	private $__s_place_region = null;
	/** 所有的场所列表 */
	private $__place_list = array();
	/** 查询字段的规则定义 */
	private $__fields = array();
	/** 结果列表总数 */
	private $__total = 0;
	/** 场所所在的区域信息 */
	private $__placeregion = array();
	/** 场所相关人列表 */
	private $__placemember = array();

	public function __construct() {
		parent::__construct();
		if ($this->__s_place === null) {
			$this->__s_place = new voa_s_oa_common_place();
			$this->__s_place_member = new voa_s_oa_common_place_member();
			$this->__s_place_region = new voa_s_oa_common_place_region();
		}
	}

	/**
	 * 场地列表
	 * @param array $request 请求的字段
	 * + placeid array 门店ID，可选
	 * + name string 门店名称
	 * + placeregionid number 分区id
	 * + uid array 相关人员
	 * + placetypeid number 所属类型，必须提供
	 * + address string 地点
	 * + remove number 是否读取已删除了的
	 * @param array $result (引用结果)
	 * @param array $options 其他参数
	 * + page 当前页码
	 * + limit 每页显示数
	 * + from_db 1=强制从数据库读取，0=缓存
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 字段规则定义
		$this->__fields = array(
			'placeid' => array('placeid', parent::VAR_ARR, array(), null, true),
			'name' => array('name', parent::VAR_STR, array(), null, true),
			'placeregionid' => array('placeregionid', parent::VAR_INT, array(), null, true),
			'uid' => array('uid', parent::VAR_ARR, array(), null, true),
			'placetypeid' => array('placetypeid', parent::VAR_INT, array(), null, false),
			'address' => array('address', parent::VAR_STR, array(), null, true),
			'lng' => array('lng', parent::VAR_STR, array(), null, true),
			'lat' => array('lat', parent::VAR_STR, array(), null, true),
			'remove' => array('remove', parent::VAR_INT, array(), null, true),
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $this->__fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 其他参数检查
		$option_fields = array(
			'page' => array('page', parent::VAR_ABS, array(), null, false),
			'limit' => array('limit', parent::VAR_ABS, array(), null, false),
			'from_db' => array('from_db', parent::VAR_INT, array(), null, false),
		);
		if (!$this->extract_field($this->__options, $option_fields, $options)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		if ($this->__options['page'] < 1) {
			$this->__options['page'] = 1;
		}
		if ($this->__options['limit'] < 1) {
			$this->__options['limit'] = 20;
		}

		// 读取缓存信息
		$cache = $this->__s_place->get_place_cache(!empty($this->__options['from_db']));
		$this->__place_list = isset($cache['data']) ? $cache['data'] : array();

		// 如果列表数据为空，则不进行操作直接返回结果
		if (empty($this->__place_list)) {
			$result = array();
			$this->__set_result($result);
			return true;
		}

		if (!isset($this->__request['remove'])) {
			$this->__request['remove'] = 0;
		}

		// 需要传入给 service 的条件
		$_conds = array();

		// 根据人员权限获取相关场地id列表
		if (!empty($this->__request['uid'])) {
			$_placeids = $this->__s_place_member->list_placeid_by_uid($this->__request['placetypeid'], $this->__request['uid']);
			if ($_placeids) {
				$_conds['placeid'] = $_placeids;
			}
		}

		// 指定了具体的门店ID
		if (!empty($this->__request['placeid'])) {
			$tmp = !empty($_conds['placeid']) ? $_conds['placeid'] : array();
			foreach ($this->__request['placeid'] as $_placeid) {
				if (!$tmp || !in_array($_placeid, $tmp)) {
					$_conds['placeid'][] = $_placeid;
				}
			}
		}

		// 需要传入给service的外部字段
		$_fields = array('name', 'placeregionid', 'placetypeid', 'address', 'remove');
		// 找到设置了条件的字段
		foreach ($_fields as $_key) {
			if ($_key == 'remove') {
				if (isset($this->__request[$_key])) {
					$_conds[$_key] = $this->__request[$_key];
				}
				continue;
			}
			if (isset($this->__request[$_key]) && !empty($this->__request[$_key])) {
				$_conds[$_key] = $this->__request[$_key];
			}
		}

		// 找到列表
		$list = $this->__s_place->list_by_conds($_conds);
		// 计算总数
		$this->__total = count($list);
		$this->__result = array();
		if ($this->__total > 0) {
			// 总页码
			$pages = ceil($this->__total/$this->__options['limit']);
			if ($this->__options['page'] > $pages) {
				$this->__options['page'] = 1;
			}
			$start = ($this->__options['page'] - 1) * $this->__options['limit'];
			if (!empty($list)) {
				// 取出当前页码内的场所列表
				$this->__result = array_slice($list, $start, $this->__options['limit']);
			} else {
				$this->__result = array();
				$this->__total = 0;
			}
		}
		$this->__set_result($result);

		return true;
	}

	/**
	 * 设置输出结果
	 * @param array $result (引用结果)
	 */
	private function __set_result(&$result) {
		$fields = array();
		foreach ($this->__fields as $_key => $_arr) {
			$fields[$_key] = isset($this->__request[$_key]) ? $this->__request[$_key] : '';
		}
		$this->__get_placeregion($this->__placeregion);
		$this->__get_placemember($this->__placemember);
		$result = array(
			'result' => $this->__result,// 结果列表集合
			'option' => array(
				'page' => $this->__options['page'],// 页码
				'limit' => $this->__options['limit'],// 每页数量
				'total' => $this->__total,// 结果总数
			),
			'fields' => $fields,// 搜索条件
			'placeregion' => $this->__placeregion, // 场所与区域对应关系
			'placemember' => $this->__placemember, // 场所与人员对应关系
		);
	}

	/**
	 * 获取场所相关的区域信息（包含区域家谱信息，以deepin深度为键名）
	 * @param array $place_region_list (引用结果)
	 * @return boolean
	 */
	private function __get_placeregion(&$place_region_list) {

		// 场所为空，则输出为空
		if (empty($this->__result)) {
			$place_region_list = array();
			return true;
		}

		// 所在类型
		$placetypeid = $this->__request['placetypeid'];
		// 所有区域ID
		$placeregionids = array();
		// 场所与区域id对应关系
		$placeid_placeregionid_list = array();

		// 遍历场所提取，所在区域与场所Id对应关系
		foreach ($this->__result as $_place) {

			// 提取所在类型
			if (!$placetypeid) {
				$placetypeid = $_place['placetypeid'];
			}
			// 当前场所所在区域id
			$_placeregionid = $_place['placeregionid'];
			// 所有场所所在区域id
			if (!isset($placeregionids[$_placeregionid])) {
				$placeregionids[$_placeregionid] = $_placeregionid;
			}
			// 场所与区域对应
			$placeid_placeregionid_list[$_place['placeid']] = $_placeregionid;
		}
		unset($_placeid, $_placeregionid);

		// 获取所有相关的区域信息（家谱）
		$parents = $this->__s_place_region->get_parents($placetypeid, $placeregionids);

		// 遍历提取场所与所在区域对应
		foreach ($placeid_placeregionid_list as $_placeid => $_placeregionid) {
			$place_region_list[$_placeid] = isset($parents[$_placeregionid]) ? $parents[$_placeregionid] : array();
		}

		return true;
	}

	/**
	 * 获取所有场所相关人员列表
	 * @param array $place_member_list (引用结果)
	 * @return boolean
	 */
	private function __get_placemember(array &$place_member_list) {

		// 场所为空，则输出为空
		if (empty($this->__result)) {
			$place_member_list = array();
			return true;
		}

		// 所有场所id
		$placeids = array();
		// 遍历提取所有场所id
		foreach ($this->__result as $_place) {
			$placeids[] = $_place['placeid'];
		}
		// 所有所有场所相关人员列表
		$place_member_list = $this->__s_place_member->get_places_uid_list($placeids);

		return true;
	}

}
