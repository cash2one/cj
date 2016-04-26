<?php
/**
 * get.php
 * uda/获取指定人员所绑定的门店、区域关系
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_member_get extends voa_uda_frontend_common_place_abstract {

	/** 请求的参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他配置参数 */
	private $__option = array();
	/** place member service */
	private $__s_place_member = null;

	/**
	 * 获取指定人员所绑定的指定类型下的所有场所和区域
	 * @param array $request
	 * + placetypeid number 场所类型ID，必须
	 * + uid array 指定人员uid，必须
	 * @param array $result
	 * <pre>
	 * array(
	 * 	'uid' => array(
	 * 		'place' => array(
	 * 			voa_d_oa_common_place_member::LEVEL_CHARGE => array(...),
	 * 			voa_d_oa_common_place_member::LEVEL_NORMAL => array(...)
	 * 		),
	 * 		'placeregion' => array(
	 * 			voa_d_oa_common_place_member::LEVEL_CHARGE => array(...),
	 * 			voa_d_oa_common_place_member::LEVEL_NORMAL => array(...)
	 * 		)
	 * 	)
	 * 	...
	 * )</pre>
	 * @param array $option
	 * @return boolean
	 */
	public function doit(array $request, array &$result, array $option = array()) {

		$this->__option = $option;
		// 请求字段定义
		$fields = array(
			'uid' => array('uid', parent::VAR_ARR, null, null, false),
			'placetypeid' => array('placetypeid', parent::VAR_INT, null, null, false),
		);
		// 字段参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$this->__request['uid'] = rintval($this->__request['uid'], true);

		// 未提供待查找的uid，则直接返回
		if (empty($this->__request['uid']) || empty($this->__request['placetypeid'])) {
			$result = array();
			return true;
		}

		$this->__s_place_member = new voa_s_oa_common_place_member();

		// 原始数据列表
		$list = $this->__s_place_member->list_all_by_uid($this->__request['placetypeid'], $this->__request['uid']);

		// 初始化结果集
		$result = array();
		foreach ($this->__request['uid'] as $_uid) {
			$result[$_uid] = array(
				'place' => array(
					voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
					voa_d_oa_common_place_member::LEVEL_NORMAL => array()
				),
				'placeregion' => array(
					voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
					voa_d_oa_common_place_member::LEVEL_NORMAL => array()
				)
			);
		}

		// 没有结果直接返回
		if (empty($list)) {
			return true;
		}

		// 格式化列表以输出
		foreach ($list as $_pm) {

			// 标记当前行的类型（场所还是区域）
			$type = $_pm['placeid'] ? 'place' : 'placeregion';
			// 类型的id字段（场所id or 区域id）
			$typeid_field = $_pm['placeid'] ? 'placeid' : 'placeregionid';
			// id值
			$typeid = $_pm[$typeid_field];
			// 权限级别
			$level = $_pm['level'];

			// 绑定为所有人的
			if (!$_pm['uid']) {
				foreach ($this->__request['uid'] as $_uid) {
					$result[$_uid][$type][$level][$typeid] = $typeid;
				}
				unset($_uid);
				continue;
			}

			// 具体人员
			$result[$_pm['uid']][$type][$level][$typeid] = $typeid;
		}

		return true;
	}

}
