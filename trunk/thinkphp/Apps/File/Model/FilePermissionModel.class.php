<?php
/**
 * FilePermissionModel.class.php
 * @create-time: 2015-07-02
 */
namespace File\Model;

class FilePermissionModel extends AbstractModel {

	// 成员类型：组长
	const MT_CHARGEHAND = 15;

	// 成员类型：协作者
	const MT_COLLABORATORS = 12;

	// 成员类型：浏览者
	const MT_BROWSE = 8;

	// 成员类型：其他
	const MT_OTHER = 4;

	// 成员标识：用户选择
	const SM_USER_CHECK = 1;

	// 成员标识：逻辑添加
	const SM_LOGIC_ADD = 2;

	// 顶级部门标识
	const MARK_CD_ID = 0;

	// 获取成员组长类型
	public function get_mt_chargehand() {

		return self::MT_CHARGEHAND;
	}

	// 获取成员协作者类型
	public function get_mt_collaborators() {

		return self::MT_COLLABORATORS;
	}

	// 获取成员浏览者类型
	public function get_mt_browse() {

		return self::MT_BROWSE;
	}

	// 获取成员其他类型
	public function get_mt_other() {

		return self::MT_OTHER;
	}

	// 获取成员标识用户选择类型
	public function get_sm_user_check() {

		return self::SM_USER_CHECK;
	}

	// 获取成员标识逻辑添加类型
	public function get_sm_logic() {

		return self::SM_LOGIC_ADD;
	}

	// 获取顶级部门默认标识
	public function get_mark_check() {

		return self::MARK_CD_ID;
	}

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'p_';
	}

	/**
	 * 根据分组id获取用户权限信息
	 * @param int $f_id 分组id
	 * @param int $m_uid 用户id
	 * @return array
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function get_by_fid_uid($f_id, $m_uid = 0) {

		// sql语句
		$sql = "SELECT * FROM __TABLE__ WHERE `m_uid`=? AND `f_id`=? AND `p_status`<?";

		// 请求参数
		$params = array (
			$m_uid,
			$f_id,
			$this->get_st_delete()
		);

		// 返回数据
		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 分组成员列表（通过fid普通查找）
	 * @param int $f_id 分组id
	 * @param array $page_option 分页参数
	 * @param array $order_option 排序参数
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function list_member_by_fid($f_id, $page_option, $order_option) {

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 查询条件
		$group_inf = array (
			$f_id,
			$this->get_st_delete()
		);

		$sql = "SELECT DISTINCT(`m_uid`), `m_username` FROM __TABLE__ WHERE `f_id`=? AND `p_status`<? order by {$order_option}{$limit}";
		return $this->_m->fetch_array($sql, $group_inf);
	}

	/**
	 * 分组成员列表（通过用户名模糊查询）
	 * @param string $name 模糊查询关键字
	 * @param array $page_option 分页参数
	 * @param array $order_option 排序参数
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function list_member_by_name($f_id, $page_option, $order_option, $name) {

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 拼接要查找的模糊查询条件name
		$find_name = '%'.$name.'%';
		// 查询条件
		$group_inf = array (
			$find_name,
			$f_id,
			$this->get_st_delete()
		);

		$sql = "SELECT DISTINCT(`m_uid`), `m_username` FROM __TABLE__ WHERE `m_username` LIKE ? AND f_id=? AND `p_status`<?{$orderby}{$limit}";

		return $this->_m->fetch_array($sql, $group_inf);
	}

	/**
	 * 退出分组，删除指定分组成员
	 * @param int $f_id 分组id
	 * @param int $m_uid 用户m_uid
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function delete_member($f_id, $m_uid) {

		// 查询条件
		$group_inf = array (
			$this->get_st_delete(),
			NOW_TIME,
			$m_uid,
			$f_id,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE m_uid=? AND f_id=? AND p_status<?";

		if (!$this->_m->execsql($sql, $group_inf)) {
			E(L('_ERR_EXIT_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 获取文件分组列表
	 * @param int $m_uid 当前登录用户id
	 * @param array $page_option 分页参数
	 * @param array $order_option 排序参数
	 * @return array|bool
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function list_groups($m_uid, $page_option, $order_option) {

		$orderby = '';
		
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 获取成员类型常量
		$arr = array (
			self::MT_CHARGEHAND,
			self::MT_COLLABORATORS,
			self::MT_BROWSE
		);

		// 分组常量类型为分组1
		$level = \File\Model\FileModel::F_GROUP;
		// 查询条件
		$group_inf = array (
			$m_uid,
			$arr,
			$level,
			$this->get_st_delete(),
			$this->get_st_delete(),
		);

		// 根据用户用户m_uid获取到用户身份及分组id
		$sql = "SELECT DISTINCT(f.f_id), f.f_name, p.p_m_type FROM `oa_file` f LEFT JOIN oa_file_permission p ON f.group_id = p.f_id WHERE p.m_uid=? AND p.p_m_type in(?) AND f.f_level=? AND f.f_status<? AND p.p_status<?{$orderby}{$limit}";

		return $this->_m->fetch_array($sql, $group_inf);

	}

	/**
	 * 根据分组id删除分组所有相关权限信息
	 * @param $fid 分组id
	 * @return boolean
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all($fid) {

		// 分组id
		$f_id = (int)$fid;

		$group_inf = array (
			$this->get_st_delete(),
			NOW_TIME,
			$f_id,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE f_id=? AND p_status<?";
		if (!$this->_m->execsql($sql, $group_inf)) {
			E(L('_ERR_DELETE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 根据分组id删除组长外用户权限信息
	 * @param $fid 分组id
	 * @return boolean
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all_member($fid) {

		// 分组id
		$f_id = (int)$fid;

		$group_inf = array (
			$this->get_st_delete(),
			NOW_TIME,
			$f_id,
			$this->get_mt_collaborators(),
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE f_id=? AND p_m_type=? AND p_status<?";

		// 执行删除操作
		$this->_m->execsql($sql, $group_inf);
		return true;
	}

	/**
	 * 根据分组id判断是否返回全体成员
	 * @param $f_id 分组id
	 * @return array
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function is_all($fid) {

		// 分组id
		$f_id = (int)$fid;
		// 默认当m_uid=0，m_username=''，cd_id=0，cd_name=''时为全体成员
		$group_inf = array (
			0, // m_uid
			'', // m_username
			0, // cd_id
			'', // cd_name
			$this->get_st_delete(),
			$f_id
		);

		$sql = "SELECT Count(*) FROM __TABLE__ WHERE m_uid=? AND m_username=? AND cd_id=? AND cd_name=? AND p_status<? AND f_id=?";;
		return $this->_m->result($sql, $group_inf);
	}

	/**
	 * 根据分组id获取组长信息
	 * @param $params 分组id
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_leader($fid) {

		// 分组id
		$f_id = (int)$fid;

		// 查询条件
		$group_inf = array (
			$this->get_mt_chargehand(),
			$this->get_st_delete(),
			$f_id
		);

		$sql = "SELECT m_uid, m_username FROM __TABLE__  WHERE p_m_type=? AND p_status<? AND f_id=?";
		return $this->_m->fetch_row($sql, $group_inf);
	}

	/**
	 * 根据分组id查找组长并修改其信息
	 * @param int $group_id 分组id
	 * @param array $group_leader_inf 分组组长信息
	 * @return boolean
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function edit_group_leader($group_id, $group_leader_inf) {

		$group_leader_array = array (
			$group_leader_inf['m_uid'],
			$group_leader_inf['m_username'],
			$this->get_st_update(),
			NOW_TIME,
			$group_id,
			$this->get_mt_chargehand(),
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET m_uid=?, m_username=?, p_status=?, p_updated=? WHERE f_id=? AND p_m_type=? AND p_status<?";

		$this->_m->execsql($sql, $group_leader_array);
		return true;
	}

	/**
	 * 根据分组id获取用户信息
	 * @param int $fid 分组id
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_member($fid) {

		// 分组id
		$f_id = (int)$fid;

		// 查询条件
		$group_inf = array (
			$this->get_mt_collaborators(),
			$this->get_sm_user_check(),
			$this->get_st_delete(),
			$f_id
		);

		$sql = "SELECT * FROM __TABLE__ WHERE  p_m_type=? AND p_sel_mark=? AND p_status<? AND f_id=?";
		return $this->_m->fetch_array($sql, $group_inf);
	}


	/**
	 * 根据分组id获取部门信息
	 * @param int $fid 分组id
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_depart($fid) {

		// 分组id
		$f_id = (int)$fid;

		// 查询条件
		$group_inf = array (
			$this->get_mt_collaborators(),
			0,
			$this->get_sm_logic(),
			$this->get_st_delete(),
			$f_id
		);

		$sql = "SELECT DISTINCT cd_id, cd_name, p_mark_cd_id FROM __TABLE__ WHERE  p_m_type=? AND cd_id !=? AND p_sel_mark=? AND p_status<? AND f_id=?";
		return $this->_m->fetch_array($sql, $group_inf);
	}


	/**
	 * 获取分组权限表中的部门id
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_depart_id() {

		$depart_inf = array (
			0, // cd_id不为0
			$this->get_sm_logic(),
			$this->get_st_delete()
		);

		$sql = "SELECT DISTINCT(cd_id) FROM __TABLE__ WHERE cd_id!=? and p_sel_mark=? and p_status<?";
		return $this->_m->fetch_array($sql, $depart_inf);
	}

	/**
	 * 根据部门id获取用户信息
	 * @param array $departids 多个部门id
	 * @return array
	 * 
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_depart_member($departids) {

		// 分组id
		$depart_ids = (array)$departids;

		// 查询条件
		$member_inf = array (
			$this->get_sm_logic(),
			$this->get_st_delete(),
			$depart_ids
		);

		$sql = "SELECT DISTINCT m_uid, m_username, cd_id FROM __TABLE__ WHERE  p_sel_mark=? AND p_status<? AND cd_id in (?)";
		return $this->_m->fetch_array($sql, $member_inf);
	}

	/**
	 * 获取权限表中除组长外用户添加的数据
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_all_member_by_user() {

		// 查询条件
		$member_inf = array (
			$this->get_mt_collaborators(),
			$this->get_sm_user_check(),
			$this->get_st_delete()
		);

		$sql = "SELECT DISTINCT m_uid, m_username FROM __TABLE__ WHERE  p_m_type=? AND p_sel_mark=? AND p_status<?";
		return $this->_m->fetch_array($sql, $member_inf);
	}



	/**
	 * 删除权限表中多余的成员
	 * @param int $cd_id 部门id
	 * @param array $m_uids 成员id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_uid($cd_id, $m_uids) {

		$member_array = array (
			$this->get_st_delete(),
			NOW_TIME,
			$this->get_sm_logic(),
			$cd_id,
			$m_uids,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE p_sel_mark=? AND cd_id=? AND m_uid in(?) AND p_status<?";

		// 执行删除操作
		$this->_m->update($sql, $member_array);
		return true;
	}

	/**
	 * 获取权限表中分组id、部门id,部门标识id
	 * @param int $depart_id 部门id
	 * @return array
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_group($depart_id) {

		// 分组id
		$depart_id = (int)$depart_id;

		// 查询条件
		$member_inf = array (
			$this->get_sm_logic(),
			$this->get_st_delete(),
			$depart_id
		);

		$sql = "SELECT DISTINCT f_id, cd_id, p_mark_cd_id FROM __TABLE__ WHERE  p_sel_mark=? AND p_status<? AND cd_id=?";
		return $this->_m->fetch_array($sql, $member_inf);
	}

	/**
	 * 返回部门信息
	 * @param int $department_id 部门id
	 * @param array $department_list 部门列表信息
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_depart_inf($department_id, $department_list) {

		foreach ($department_list as $department_inf) {
			if ($department_inf['cd_id'] == $department_id) {
				return array (
					'cd_id'   => $department_inf['cd_id'],
					'cd_name' => $department_inf['cd_name']
				);
			}
		}

		return true;
	}

	/**
	 * 删除权限表中的分组下的某部门成员
	 * @param array $cdids 多个部门id
	 * @param int $fid 分组id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_cd_id($cdids, $fid) {

		// 部门id
		$cd_ids = (array)$cdids;

		// 分组id
		$f_id = (int) $fid;

		$depart_array = array (
			$this->get_st_delete(),
			NOW_TIME,
			$this->get_sm_logic(),
			$cd_ids,
			$f_id,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE p_sel_mark=? AND cd_id in(?) AND f_id=? AND p_status<?";

		// 执行删除操作
		if (!$this->_m->update($sql, $depart_array)) {

			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 删除权限表中的分组下的某成员
	 * @param array $muids 多个成员id
	 * @param int $fid 分组id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_m_uid($muids, $fid) {

		// 成员id
		$m_uids = (array)$muids;

		// 分组id
		$f_id = (int)$fid;

		$member_array = array (
			$this->get_st_delete(),
			NOW_TIME,
			$this->get_sm_user_check(),
			$this->get_mt_collaborators(),
			$m_uids,
			$f_id,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE p_sel_mark=? AND p_m_type=? AND m_uid in(?) AND f_id=? AND p_status<?";

		// 执行删除操作
		if (!$this->_m->update($sql, $member_array)) {

			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 删除权限表中的已不存在的部门成员
	 * @param array $cd_ids 多个部门id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_depart($cd_ids) {

		// 部门id
		$cd_ids = (array)$cd_ids;

		$member_array = array (
			$this->get_st_delete(),
			NOW_TIME,
			$this->get_sm_logic(),
			$this->get_mt_collaborators(),
			$cd_ids,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE p_sel_mark=? AND p_m_type=? AND cd_id in(?) AND p_status<?";

		// 执行删除操作
		if (!$this->_m->update($sql, $member_array)) {

			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 删除权限表中的用户添加的已不存在的成员
	 * @param array $member_ids 多个成员id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_member_by_id($member_ids) {

		// 部门id
		$member_ids = (array)$member_ids;

		$member_array = array (
			$this->get_st_delete(),
			NOW_TIME,
			$this->get_sm_user_check(),
			$this->get_mt_collaborators(),
			$member_ids,
			$this->get_st_delete()
		);

		$sql = "UPDATE __TABLE__ SET p_status=?, p_deleted=? WHERE p_sel_mark=? AND p_m_type=? AND m_uid in(?) AND p_status<?";

		// 执行删除操作
		if (!$this->_m->update($sql, $member_array)) {

			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;

	}

}
