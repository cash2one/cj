<?php
/**
 * get.php
 * 获取门店相关信息（包含所在区域的信息）
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_get extends voa_uda_frontend_common_place_abstract {

	/** 请求的参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他内部参数 */
	private $__option = array();

	/** place service */
	private $__s_place = NULL;
	/** place region service */
	private $__s_place_region = null;
	/** place member service */
	private $__s_place_member = null;

	/**
	 * 获取指定门店的相关信息，包含所在区域的信息
	 * @param array $request 请求的参数
	 * + placeid 门店ID
	 * + remove 是否读取已“删除”的，1=是，0=否，默认：0=只读取未“删除”的
	 * @param array $result (引用结果)返回结果
	 * @param array $option 其他内部配置参数
	 * @return boolean
	 */
	public function doit($request = array(), &$result = array(), $option = array()) {

		// 赋值内部成员
		$this->__option = $option;
		// 定义字段规则
		$fields = array(
			'placeid' => array('placeid', parent::VAR_ABS, null, null, false),
			'remove' => array('remove', parent::VAR_ABS, null, null, true)
		);
		// 基本过滤检查
		if (!$this->extract_field($this->__request, $fields, $request, true)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		if (empty($this->__request['remove'])) {
			$this->__request['remove'] = 0;
		} else {
			$this->__request['remove'] = 1;
		}

		$this->__s_place = new voa_s_oa_common_place();
		$this->__s_place_region = new voa_s_oa_common_place_region();
		$this->__s_place_member = new voa_s_oa_common_place_member();

		// 场所缓存
		$place = $this->__s_place->get_place_by_placeid($this->__request['placeid'], true, $this->__request['remove']);
		// 如果不存在
		if (empty($place)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACEID_NOT_EXISTS, $this->__request['placeid']);
		}

		// 获取全部父级信息
		$region = $this->__s_place_region->get_placeregionid_parent($place['placetypeid'], $place['placeregionid']);

		// 获取指定门店相关联的人员ID列表
		$user_list = $this->__s_place_member->get_place_member_list($this->__request['placeid'], 'all');

		// 返回结果
		$result = array(
			'place' => $place,
			'region' => $region,
			'user_list' => $user_list
		);

		return true;
	}

}
