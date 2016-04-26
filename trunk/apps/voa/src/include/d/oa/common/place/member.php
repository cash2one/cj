<?php
/**
 * member.php
 * 场所表 - 相关人员表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_place_member extends voa_d_abstruct {

	/** 相关人员级别：负责人 */
	const LEVEL_CHARGE = 1;
	/** 相关人员级别：普通人 */
	const LEVEL_NORMAL = 2;

	/** 所有人员可见 */
	const IS_ALL = 0;
	/** 区域人员 */
	const IS_REGION_USER = 0;
	/** 场所地点人员 */
	const IS_PLACE_USER = 0;

	/** 相关人员所属类型：场所地点人员 */
	const TYPE_PLACE = 'place';
	/** 相关人员所属类型：场所区域人员 */
	const TYPE_REGION = 'region';

	/** 初始化 */
	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.common_place_member';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'placememberid';

		parent::__construct();
	}

	/**
	 * 获取指定场所的人员列表
	 * @param number $placeid
	 * @param mixed $level 可选值：LEVEL_*类常量，其他值获取全部
	 * @return array
	 */
	public function list_by_placeid($placeid, $level) {

		$conds = array('placeid' => $placeid);
		if ($level == self::LEVEL_CHARGE) {
			$conds['level'] = self::LEVEL_CHARGE;
		} elseif ($level == self::LEVEL_NORMAL) {
			$conds['level'] = self::LEVEL_NORMAL;
		}

		return parent::list_by_conds($conds);
	}

	/**
	 * 获取指定分区的人员列表
	 * @param number $placeregionid 分区ID
	 * @param mixed $level 可选值：LEVEL_*类常量，其他值获取全部
	 * @return array
	 */
	public function list_by_placeregionid($placeregionid, $level) {

		$conds = array('placeregionid' => $placeregionid);
		if ($level == self::LEVEL_CHARGE) {
			$conds['level'] = self::LEVEL_CHARGE;
		} elseif ($level == self::LEVEL_NORMAL) {
			$conds['level'] = self::LEVEL_NORMAL;
		}

		return parent::list_by_conds($conds);
	}

	/**
	 * 获取指定人员所关联的场所(placeid/placeregionid)相关信息
	 * @param number $placetypeid
	 * @param number|array $uid
	 * @param string $get_place_type placeid=获取场所id，placeregionid=获取场所区域id，默认=所有
	 * @param string $level
	 * @return array
	 */
	public function list_by_uid($placetypeid, $uid, $get_place_type = 'all', $level = 'all') {

		$conds = array();
		// 指定类型
		$conds['placetypeid'] = $placetypeid;
		// 指定场所
		$field = '';
		if ($get_place_type == 'placeid') {
			// 获取场所列表
			$conds['placeregionid'] = self::IS_PLACE_USER;
			$field = $get_place_type;
		} elseif ($get_place_type == 'placeregionid') {
			// 获取场地列表
			$conds['placeid'] = self::IS_REGION_USER;
			$field = $get_place_type;
		}
		// 指定人员
		if ($uid) {
			if ($field) {
				$uid[] = 0;
				$conds[$field] = $uid;
			}
		}
		// 指定权限级别
		if ($level == self::LEVEL_CHARGE) {
			// 负责人
			$conds['level'] = self::LEVEL_CHARGE;
		} elseif ($level == self::LEVEL_NORMAL) {
			// 其他相关人
			$conds['level'] = self::LEVEL_NORMAL;
		}

		return (array) parent::list_by_conds($conds);
	}

	/**
	 * 获取指定人员所有的绑定的区域和场所id列表
	 * @param number $placetypeid
	 * @param array $uid
	 * @return array
	 */
	public function list_all_by_uid($placetypeid, $uid) {

		$uid[] = 0;

		$find_uid = array();
		$this->_field_sign_condi($find_uid, "uid in (?)", $uid);

		$sql = "placetypeid=? AND {$find_uid[0]} AND status<? AND (placeid>0 OR placeregionid>0)";
		$data = array();
		$data[] = $placetypeid;
		foreach ($uid as $_uid) {
			$data[] = $_uid;
		}
		$data[] = parent::STATUS_DELETE;

		return (array) parent::_list_by_complex($sql, $data, 0);
	}

}
