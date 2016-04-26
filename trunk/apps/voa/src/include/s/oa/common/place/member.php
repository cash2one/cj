<?php
/**
 * member.php
 * service/公共模块/场所管理/相关人员表操作
 * validator_xx 验证方法，出错抛错
 * check_xx 检查方法，返回boolean
 * format_xx 格式化方法，引用返回结果
 * get_xx 获取数据
 * list_xxx 读取数据列表相关
 * ... 其他自定方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place_member extends voa_s_oa_common_place_abstract {

	/** 多id之间的分隔符 */
	public $uid_comma = ',';

	/** orm place member */
	private $__d_place_member = null;

	public function __construct() {
		parent::__construct();
		if ($this->__d_place_member === null) {
			$this->__d_place_member = new voa_d_oa_common_place_member();
		}
	}

	/**
	 * 获取指定场所关联的人员列表，按权限级别列表
	 * @param number $placeid 场所ID
	 * @param string $level 获取指定级别的人员all=全部，其他见voa_d_oa_common_place_member::LEVEL_*定义。默认：all。
	 * @return multitype:
	 */
	public function get_place_member_list($placeid, $level = 'all') {

		// 根据所需要的请求，初始化
		$list = array();
		if ($level == voa_d_oa_common_place_member::LEVEL_CHARGE || $level == voa_d_oa_common_place_member::LEVEL_NORMAL) {
			$list[$level] = array();
		} else {
			$list = array(
				voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
				voa_d_oa_common_place_member::LEVEL_NORMAL => array()
			);
		}
		$tmp = $this->__d_place_member->list_by_placeid($placeid, $level);
		if (empty($tmp)) {
			return $list;
		}
		foreach ($tmp as $_m) {
			$list[$_m['level']][$_m['placememberid']] = $_m;
		}

		return $list;
	}

	/**
	 * 获取指定分区相关人员列表，按权限级别列表
	 * @param number $placeregionid
	 * @param string $level 获取指定级别的人员all=全部，其他见voa_d_oa_common_place_member::LEVEL_*定义。默认：all。
	 * @return Ambigous <multitype:, Ambigous, boolean, unknown, multitype:unknown >
	 */
	public function get_place_region_member_list($placeregionid, $level = 'all') {

		$list = array();
		$tmp = $this->__d_place_member->list_by_placeregionid($placeregionid, $level);
		if (!empty($tmp)) {
			$list[voa_d_oa_common_place_member::LEVEL_CHARGE] = array();
			$list[voa_d_oa_common_place_member::LEVEL_NORMAL] = array();
			foreach ($tmp as $_m) {
				$list[$_m['level']][$_m['placememberid']] = $_m;
			}
			unset($_m);
		}
		unset($tmp);

		return $list;
	}

	/**
	 * 获取给定的场所id列表获取对应的全部人员列表信息以场所id为键名
	 * @param array $placeids
	 * @param string $level
	 * @return array
	 */
	public function get_places_uid_list($placeids, $level = 'all') {

		// 默认的全部级别
		$levels = array(
			voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
			voa_d_oa_common_place_member::LEVEL_NORMAL => array()
		);

		// 返回的数据
		$list = array();

		// 获取全部人员列表
		$tmp = $this->__d_place_member->list_by_placeid($placeids, $level);
		if (empty($tmp)) {
			return $list;
		}
		// 遍历以提取按场所id为键名的列表
		foreach ($tmp as $_m) {

			// 场所id
			$_placeid = $_m['placeid'];
			// 场所人员uid
			$_uid = $_m['uid'];
			// 级别
			$_level = $_m['level'];
			// 初始化
			if (!isset($list[$_placeid])) {
				$list[$_placeid] = $levels;
			}

			$list[$_placeid][$_level][$_uid] = $_m;
		}

		return $list;
	}

	/**
	 * 获取给定的区域id列表获取对应的全部人员列表信息以区域ID为键名
	 * @param array $placeregionids
	 * @param string $level
	 * @return array
	 */
	public function get_placeregion_uid_list($placeregionids, $level) {

		// 默认的全部级别
		$levels = array(
			voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
			voa_d_oa_common_place_member::LEVEL_NORMAL => array()
		);

		// 返回的数据
		$list = array();

		// 获取全部人员列表
		$tmp = $this->__d_place_member->list_by_placeregionid($placeregionids, $level);
		// 遍历以提取按场所id为键名的列表
		foreach ($tmp as $_m) {

			// 区域id
			$_placeregionid = $_m['placeregionid'];
			// 区域人员uid
			$_uid = $_m['uid'];
			// 级别
			$_level = $_m['level'];
			// 初始化
			if (!isset($list[$_placeregionid])) {
				$list[$_placeregionid] = $levels;
			}

			$list[$_placeregionid][$_level][$_uid] = $_m;
		}

		return (array)$list;
	}

	/**
	 * 获取指定uid所关联的场所id
	 * @param number $placetypeid 场所类型
	 * @param mixed $uid 人员id，可以是数组，也可以是以半角逗号分隔的字符串，也可以是整型
	 * @param string $level 要获取的权限级别。voa_d_oa_common_place_member::LEVEL_*常量获取具体。all=全部。默认：全部
	 * @return array
	 */
	public function list_placeid_by_uid($placetypeid, $uid, $level = 'all') {

		if (!is_array($uid)) {
			$uid = explode($this->uid_comma, $uid);
		}

		$uids = array();
		foreach ($uid as $_uid) {
			$_uid = trim($_uid);
			if (!is_numeric($_uid) || isset($uids[$_uid])) {
				continue;
			}
			$uids[$_uid] = $_uid;
		}
		unset($_uid);

		// 遍历数据获取placeid列表
		$placeids = array();
		foreach ($this->__d_place_member->list_by_uid($placetypeid, $uids, 'placeid', $level) as $_pm) {
			if ($_pm['placeid'] && !isset($placeids[$_pm['placeid']])) {
				$placeids[$_pm['placeid']] = $_pm['placeid'];
			}
		}

		return $placeids;
	}

	/**
	 * 获取指定人员所有相关区域和场所id
	 * @param number $placetypeid
	 * @param array $uid
	 * @param string $level
	 */
	public function list_all_by_uid($placetypeid, $uid) {

		if (!is_array($uid)) {
			$uid = explode($this->uid_comma, $uid);
		}

		$uids = array();
		foreach ($uid as $_uid) {
			$_uid = trim($_uid);
			if (!is_numeric($_uid) || isset($uids[$_uid])) {
				continue;
			}
			$uids[$_uid] = $_uid;
		}
		unset($_uid);

		return $this->__d_place_member->list_all_by_uid($placetypeid, $uids);
	}

	/**
	 * 删除指定场所的所有相关人员
	 * @param number|array $placeid 场所ID
	 * @return boolean
	 */
	public function delete_place_member($placeid) {

		$conds = array();
		$conds['placeid'] = $placeid;

		$d_place_member = new voa_d_oa_common_place_member();
		$d_place_member->delete_by_conds($conds);

		return true;
	}

	/**
	 * 删除指定分区的所有相关人员
	 * @param number|array $placeregionid
	 * @return boolean
	 */
	public function delete_place_region_member($placeregionid) {

		$conds = array();
		$conds['placeregionid'] = $placeregionid;

		$d_place_member = new voa_d_oa_common_place_member();
		$d_place_member->delete_by_conds($conds);

		return true;
	}

	/**
	 * 删除场所相关人员（只删除）
	 * @param array $uids 人员uid列表 array(1, 2, ....)
	 * @param string $placeid $placeid 场地ID。区域人员则为 0，默认：null=全部
	 * @param string $placeregionid $placeregionid 区域ID。场所人员则为0，默认：null=全部
	 * @param string $level $level 权限级别，可选值：voa_d_oa_common_place::LEVEL_* 默认：null=全部
	 * @return boolean
	 */
	public function delete_member(array $uids, $placeid = null, $placeregionid = null, $level = null) {

		if (empty($uids)) {
			return true;
		}

		$conds = array();
		// 指定了场所
		if ($placeid !== null) {
			$conds['placeid'] = $placeid;
		}
		// 指定了区域
		if ($placeregionid !== null) {
			$conds['placeregionid'] = $placeregionid;
		}
		// 指定了级别
		if ($level !== null) {
			$conds['level'] = $level;
		}

		$d_place_member = new voa_d_oa_common_place_member();
		$d_place_member->delete_by_conds($conds);

		return true;
	}

	/**
	 * 更新场所相关人员（新增、删除），这是新增、更新相关人员的主方法
	 * @param array $new_uids 新提交的人员ID列表 array(1,2,...)
	 * @param array $old_uids 旧的人员列表，默认：array()
	 * @param number $placetypeid 场所类型ID
	 * @param number $placeid $placeid 场地ID。区域人员则为 0，默认：null=全部
	 * @param number $placeregionid $placeregionid 区域ID。场所人员则为0，默认：null=全部
	 * @param number $level 权限级别，可选值：voa_d_oa_common_place::LEVEL_* 默认：null=全部
	 * @return boolean
	 */
	public function update_member(array $new_uids, array $old_uids, $placetypeid, $placeid, $placeregionid, $level) {

		// 提取需要删除和新增的人员

		// 需要移除的人
		$deleted = array();
		foreach ($old_uids as $_uid) {
			if (!isset($new[$_uid])) {
				$deleted[] = $_uid;
			}
		}
		unset($_uid);

		// 需要新增的人
		$added = array();
		foreach ($new_uids as $_uid) {
			if (!isset($old[$_uid])) {
				$added[] = $_uid;
			}
		}
		unset($_uid);

		// 删除人员
		if ($deleted) {
			$this->delete_member($deleted, $placeid, $placeregionid, $level);
		}

		// 新增人员
		if ($added) {
			$this->__add_member($added, $placetypeid, $placeid, $placeregionid, $level);
		}

		return true;
	}

	/**
	 * 格式化地点的权限人员ID
	 * @param array &$uid (引用结果)
	 * @return boolean
	 */
	public function format_place_user_uid(&$uid) {

		// 将传过来的人员ID转换为数组格式，以便于处理
		if (!is_array($uid)) {
			$uid = explode($this->uid_comma, $uid);
		}

		// 格式化后的uid数组
		$format_uid = array();
		foreach ($uid as $_uid) {
			$_uid = trim($_uid);
			if (!is_numeric($_uid)) {
				// 非数字，则忽略
				continue;
			}
			$_uid = (int)$_uid;
			if ($_uid < 1 || isset($format_uid[$_uid])) {
				// 忽略 不是uid的 和 重复的
				continue;
			}
			$format_uid[$_uid] = $_uid;
		}
		unset($_uid);

		// 人员不为空，则检查过滤有效的用户uid
		if (!empty($format_uid)) {
			$_uids = array();
			$serv = &service::factory('voa_s_oa_member');
			foreach ($serv->fetch_all_by_ids($format_uid) as $_m) {
				$_uids[] = $_m['m_uid'];
			}
			$format_uid = $_uids;
			unset($_uids, $_m);
		}

		$uid = $format_uid;
		unset($format_uid);

		return true;
	}

	/**
	 * 检查场所负责人数量
	 * @param string|array $uid (引用结果)待检查的uid
	 * @param string|array $normal_uid 相关人员id，用于剔除$uid内存在的人
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_master_uid(&$uid, $normal_uid = array()) {

		// 重新整理uid
		$this->reset_uid($uid, $normal_uid);

		// 当前设置的负责人数
		$count = count($uid);
		// 超出
		if ($count > $this->p_sets['place_master_count_max']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_MASTER_COUNT_ERROR_MAX, $this->p_sets['place_master_count_max']);
		}
		// 太少
		if ($count < $this->p_sets['place_master_count_min']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_MASTER_COUNT_ERROR_MIN, $this->p_sets['place_master_count_min']);
		}

		return true;
	}

	/**
	 * 检查场所相关人员数量
	 * @param array $uid (引用结果)
	 * @param string $master_uid 负责人uid，用于剔除$uid内存在的人
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_normal_uid(&$uid, $master_uid = array()) {

		// 重新整理uid
		$this->reset_uid($uid, $master_uid);

		// 当前设置的负责人数
		$count = count($uid);
		// 超出
		if ($count > $this->p_sets['place_normal_count_max']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_NORMAL_COUNT_ERROR_MAX, $this->p_sets['place_normal_count_max']);
		}
		// 太少
		if ($count < $this->p_sets['place_normal_count_min']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_NORMAL_COUNT_ERROR_MIN, $this->p_sets['place_normal_count_min']);
		}

		return true;
	}

	/**
	 * 检查区域负责人数量
	 * @param array $uid (引用结果)待检查的uid
	 * @param string|array $normal_uid 相关人员id，用于剔除$uid内存在的人
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_region_master_uid(&$uid, $normal_uid = array()) {

		// 重新整理uid
		$this->reset_uid($uid, $normal_uid);

		// 当前设置的负责人数
		$count = count($uid);
		// 超出
		if ($count > $this->p_sets['region_master_count_max']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_MASTER_COUNT_ERROR_MAX, $this->p_sets['region_master_count_max']);
		}
		// 太少
		if ($count < $this->p_sets['region_master_count_min']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_MASTER_COUNT_ERROR_MIN, $this->p_sets['region_master_count_min']);
		}

		return true;
	}

	/**
	 * 检查区域相关人员数量
	 * @param array $uid (引用结果)
	 * @param string $master_uid 负责人uid，用于剔除$uid内存在的人
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_region_normal_uid(&$uid, $master_uid = array()) {

		// 重新整理uid
		$this->reset_uid($uid, $master_uid);

		// 当前设置的负责人数
		$count = count($uid);
		// 超出
		if ($count > $this->p_sets['region_normal_count_max']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_NORMAL_COUNT_ERROR_MAX, $this->p_sets['region_normal_count_max']);
		}
		// 太少
		if ($count < $this->p_sets['region_normal_count_min']) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_NORMAL_COUNT_ERROR_MIN, $this->p_sets['region_normal_count_min']);
		}

		return true;
	}

	/**
	 * 验证场所相关人员的级别值是否有效（权限）
	 * @param number $level (引用)级别
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_member_level(&$level) {

		$level = (string)$level;
		if (!array_key_exists($level, $this->place_member_levels)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_MEMBER_LEVEL_ERROR, $level);
		}

		return true;
	}

	/**
	 * 重新整理给定的uid数据，剔除无效和重复的uid
	 * @param string|array $uid (引用结果)待整理的uid array
	 * @param string|array $other_level_uid 其他权限级别的uid
	 * @return boolean
	 */
	public function reset_uid(&$uid, $other_level_uid = array()) {

		// 转换为数组以便于处理
		if (!is_array($uid)) {
			$arr_uid = explode($this->uid_comma, $uid);
		} else {
			$arr_uid = $uid;
		}
		// 转为数组
		if (!is_array($other_level_uid)) {
			$other_level_uid = explode($this->uid_comma, $other_level_uid);
		}
		// 试图剔除重复的uid
		if (!empty($other_level_uid)) {
			foreach ($arr_uid as $_key => $_uid) {
				if (in_array($_uid, $other_level_uid)) {
					unset($arr_uid[$_key]);
				}
			}
		}

		// 重新整理
		$uid = $arr_uid;

		return true;
	}

	/**
	 * 批量新增场所人员（只新增不更新），不推荐单独使用
	 * @param array $uids 人员uid列表 array(1, 2, ....)
	 * @param number $placetypeid 场所类型
	 * @param number $placeid 场地ID。区域人员则为 0
	 * @param number $placeregionid 区域ID。场所人员则为0
	 * @param number $level 权限级别
	 * @return boolean
	 */
	private function __add_member(array $uids, $placetypeid, $placeid, $placeregionid, $level) {
		if (empty($uids)) {
			return true;
		}
		$data = array();
		foreach ($uids as $_uid) {
			if (isset($data[$_uid])) {
				continue;
			}
			$data[$_uid] = array(
				'placetypeid' => $placetypeid,
				'uid' => $_uid,
				'placeregionid' => $placeregionid,
				'placeid' => $placeid,
				'level' => $level
			);
		}
		if ($data) {
			$d_place_member = new voa_d_oa_common_place_member();
			$d_place_member->insert_multi($data);
		}

		return true;
	}

}
