<?php
/**
 * update.php
 * 更新场所、区域的负责人、相关人
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_place_member_update extends voa_uda_frontend_common_place_abstract {

	/** 请求的参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他配置参数 */
	private $__options = array();
	/** place member service */
	private $__s_place_member = null;
	/** place region service */
	private $__s_place_region = null;
	/** place service */
	private $__s_place = null;

	/**
	 * 更新场所相关人
	 * @param array $request 请求参数
	 * <pre>
	 * + id 场所ID（区域ID or 场所ID）
	 * + type 类型，区域 or 场所。可选值：voa_d_oa_common_place_member::TYPE_*
	 * + level 关联的人的级别 voa_d_oa_common_place_member::LEVEL_* 负责人 or 相关人
	 * + uid 人员列表，数组格式 array(1, 2, ....)，可选，不提供则不更新
	 * </pre>
	 * @param array $result (引用结果)返回结果
	 * + uid 人员列表
	 * @param array $options
	 * @throws Exception
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 参数定义规则
		$fields = array(
			'placetypeid' => array('placetypeid', parent::VAR_INT, null, null, true),
			'id' => array('id', parent::VAR_INT, null, null, true),
			'type' => array('type', parent::VAR_STR, null, null, true),
			'level' => array('level', parent::VAR_STR, null, null, true),
			'uid' => array('uid', parent::VAR_ARR, null, null, true),
		);
		// 字段参数检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		// 未提供人员ID，则不更新，直接返回
		if (!isset($this->__request['uid'])) {
			return true;
		}

		// 一旦需要更新，几个参数必须提供完整
		// 缺少必须提供的参数
		foreach (array('id', 'type', 'level') as $_key) {
			if (!isset($this->__request[$_key])) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::LOSE_MEMBER_PARAM, $_key);
			}
		}

		$this->__s_place_member = new voa_s_oa_common_place_member();

		// 未知的人员类型
		if ($this->__request['type'] != 'region' && $this->__request['type'] != 'place') {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::UNKNOW_MEMBER_TYPE, $this->__request['type']);
		}
		// 未知权限级别
		if (!$this->__s_place_member->validator_place_member_level($this->__request['level'])) {
			return false;
		}

		// 确定使用的操作方法
		$method = '__execute_';
		if ($this->__request['type'] == 'region') {
			// 更新分区人员
			$this->__s_place_region = new voa_s_oa_common_place_region();
			$method .= 'region_';
		} elseif ($this->__request['type'] == 'place') {
			// 更新场所人员
			$this->__s_place = new voa_s_oa_common_place();
			$method .= 'place_';
		}
		// 人员权限级别类型
		if ($this->__request['level'] == voa_d_oa_common_place_member::LEVEL_CHARGE) {
			$method .= 'master';
		} elseif ($this->__request['level'] == voa_d_oa_common_place_member::LEVEL_NORMAL) {
			$method .= 'normal';
		}

		// 执行具体方法
		return $this->$method();
	}

	/**
	 * 更新分区的负责人
	 * @return boolean
	 */
	private function __execute_region_master() {

		// 判断区域是否存在
		$region = $this->__s_place_region->get_place_region($this->__request['id'], true);
		if (!$region) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::MEMBER_MASTER_REGION_NONE, $this->__request['id']);
		}

		// 区域的相关人员历史列表
		$old_normal_uid = array();
		$old_master_uid = array();
		$this->__get_region_uid_list($old_master_uid, $old_normal_uid);

		// 整理区域人员
		if (!$this->__s_place_member->validator_region_master_uid($this->__request['uid'], $old_normal_uid)) {
			return false;
		}

		// 更新
		$this->__s_place_member->update_member($this->__request['uid'], $old_master_uid
				, $region['placetypeid'], voa_d_oa_common_place_member::IS_REGION_USER
				, $this->__request['id'], voa_d_oa_common_place_member::LEVEL_CHARGE);

		return true;
	}

	/**
	 * 更新分区的相关人员
	 * @return boolean
	 */
	private function __execute_region_normal() {

		// 目前业务尚无此需求

		return true;
	}

	/**
	 * 更新场所的负责人
	 * @return boolean
	 */
	private function __execute_place_master() {

		// 判断地点是否存在
		$place = $this->__s_place->get_place_by_placeid($this->__request['id'], true);
		if (!$place) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::MEMBER_MASTER_PLACE_NONE, $this->__request['id']);
		}

		// 地点的相关人员历史列表
		$old_normal_uid = array();
		$old_master_uid = array();
		$this->__get_place_uid_list($old_master_uid, $old_normal_uid);

		// 整理地点人员
		if (!$this->__s_place_member->validator_place_master_uid($this->__request['uid'], $old_normal_uid)) {
			return false;
		}

		// 更新
		$this->__s_place_member->update_member($this->__request['uid'], $old_master_uid
				, $place['placetypeid'], $this->__request['id']
				, voa_d_oa_common_place_member::IS_PLACE_USER, voa_d_oa_common_place_member::LEVEL_CHARGE);

		return true;
	}

	/**
	 * 更新场所的相关人
	 * @return boolean
	 */
	private function __execute_place_normal() {

		// 判断地点是否存在
		$place = $this->__s_place->get_place_by_placeid($this->__request['id'], true);
		if (!$place) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::MEMBER_NORMAL_PLACE_NONE, $this->__request['id']);
		}

		// 地点的相关人员历史列表
		$old_normal_uid = array();
		$old_master_uid = array();
		$this->__get_place_uid_list($old_master_uid, $old_normal_uid);

		// 整理地点人员
		if (!$this->__s_place_member->validator_place_normal_uid($this->__request['uid'], $old_master_uid)) {
			return false;
		}

		// 更新
		$this->__s_place_member->update_member($this->__request['uid'], $old_normal_uid
				, $place['placetypeid'], $this->__request['id']
				, voa_d_oa_common_place_member::IS_PLACE_USER, voa_d_oa_common_place_member::LEVEL_NORMAL);

		return true;
	}

	/**
	 * 获取当前分区的相关人员
	 * @param array $master_uid_list
	 * @param array $normal_uid_list
	 * @return boolean
	 */
	private function __get_region_uid_list(array &$master_uid_list, array &$normal_uid_list) {

		// 获取全部列表
		$list = $this->__s_place_member->get_place_region_member_list($this->__request['id']);
		$master_uid_list = $normal_uid_list = array();

		// 相关负责人
		if (!empty($list[voa_d_oa_common_place_member::LEVEL_CHARGE])) {
			$master_uid_list = $list[voa_d_oa_common_place_member::LEVEL_CHARGE];
		}

		// 相关人员
		if (!empty($list[voa_d_oa_common_place_member::LEVEL_NORMAL])) {
			$normal_uid_list = $list[voa_d_oa_common_place_member::LEVEL_NORMAL];
		}

		return true;
	}

	/**
	 * 获取当前地点的相关人员
	 * @param array $master_uid_list
	 * @param array $normal_uid_list
	 * @return boolean
	 */
	private function __get_place_uid_list(array &$master_uid_list, array &$normal_uid_list) {

		// 获取全部列表
		$list = $this->__s_place_member->get_place_member_list($this->__request['id']);
		$master_uid_list = $normal_uid_list = array();

		// 相关负责人
		if (!empty($list[voa_d_oa_common_place_member::LEVEL_CHARGE])) {
			$master_uid_list = $list[voa_d_oa_common_place_member::LEVEL_CHARGE];
		}

		// 相关人员
		if (!empty($list[voa_d_oa_common_place_member::LEVEL_NORMAL])) {
			$normal_uid_list = $list[voa_d_oa_common_place_member::LEVEL_NORMAL];
		}

		return true;
	}

}
