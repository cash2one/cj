<?php
/**
 * FilePermissionService.class.php
 * @create-time: 2015-07-02
 */
namespace File\Service;

use Common\Common\Cache;

class FilePermissionService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("File/FilePermission");
	}


	/**
	 * 判断要查找信息是否存在,若存在则返回
	 * @param array $array 被查找信息
	 * @param string $key 查找字段
	 * @param string $val 查找值
	 * @param array $data 引用返回值
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function is_exist($array, $key, $val, &$data) {

		// 判断被查找信息是否为空
		if (!empty($array)) {

			// 遍历被查找信息
			foreach ($array as $v) {

				// 比对信息，返回查找到数据
				if ($v[$key] == $val) {
					$data = $v;
					return true;
				}

			}
		}

		return false;
	}

	/**
	 * 根据用户id和分组id获取权限信息
	 * @param int $f_id 分组id
	 * @param int $type 成员类型
	 * @param int $m_uid 用户id
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function is_permission($f_id, $type = 8, $m_uid = 0) {

		// 参数非空验证
		if (empty($f_id) || empty($m_uid)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 分组id不存在
		$serv_f = D("File/File");
		if (!$serv_f->get($f_id)) {
			$this->_set_error('_ERR_GROUP_ID_ERROR');
			return false;
		}

		// 合法成员类型数组
		$permission_arr = array (
			$this->_d->get_mt_chargehand(),
			$this->_d->get_mt_collaborators(),
			$this->_d->get_mt_browse()
		);

		// 判断成员类型是否合法
		if (!in_array($type, $permission_arr)) {
			$this->_set_error('_ERR_FILE_MTYPE_INVALID');
			return false;
		}

		// 权限数据
		$pinfo = $this->_d->get_by_fid_uid($f_id, $m_uid);

		// 成员类型
		$p_m_type = (int)$pinfo['p_m_type'];

		// 判断是否有权限
		if (($p_m_type & $type) == $type) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 根据分组id删除分组所有权限信息
	 * @param int $fid 分组id
	 * @param array $condation
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all($fid) {

		// 获取分组id
		$f_id = (int)$fid;

		// 软删除对应权限信息
		if (!$this->_d->delete_all($f_id)) {
			return false;
		}

		return true;
	}

	/**
	 * 根据分组id删除组长外用户权限信息
	 * @param int $fid 分组id
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all_member($fid) {

		// 获取分组id
		$f_id = (int)$fid;

		// 软删除对应权限信息
		if (!$this->_d->delete_all_member($f_id)) {
			return false;
		}

		return true;
	}

	/**
	 * 根据分组id获取组长信息
	 * @param int $fid 分组id
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_leader($fid) {

		// 获取分组
		$f_id = (int)$fid;

		return $this->_d->get_leader($f_id);
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

		// 获取分组
		$f_id = (int)$fid;
		return $this->_d->list_member($f_id);
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

		// 获取分组
		$f_id = (int)$fid;
		return $this->_d->list_depart($f_id);
	}


	/**
	 * 根据分组id判断是否返回全体成员
	 * @param int $f_id 分组id
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function is_all($fid) {

		// 获取分组id
		$f_id = (int)$fid;

		// 判断是否获取全部成员
		if (!$this->_d->is_all($f_id)) {
			return false;
		}

		return true;
	}

	/**
	 * 根据分组id查找组长并修改其信息
	 * @param int $group_id 分组id
	 * @param array $group_leader_inf 分组组长信息
	 * @param array $extend扩展登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function edit_group_leader($group_id, $group_leader_inf, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 编辑分组
		if (!$this->_d->edit_group_leader($group_id, $group_leader_inf)) {
			return false;
		}

		return true;
	}

	/**
	 * 返回分组组长信息
	 * @param int $group_id 分组id
	 * @param array $group_leader_inf 分组组长信息
	 * @param array $extend扩展登录信息
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_leader_array($group_id, $group_leader_inf, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		return array (
			'f_id'         => $group_id,
			'm_uid'        => $group_leader_inf['m_uid'],
			'm_username'   => $group_leader_inf['m_username'],
			'p_sel_mark'   => $this->_d->get_sm_user_check(),
			'p_mark_cd_id' => $this->_d->get_mark_check(),
			'cd_id'        => 0,
			'cd_name'      => '',
			'p_m_type'     => $this->_d->get_mt_chargehand(),
			'p_status'     => $this->_d->get_st_create(),
			'p_created'    => NOW_TIME
		);
	}

	/**
	 * 返回分组成员信息
	 * @param int $group_id 分组id
	 * @param array $group_member_inf 分组成员信息
	 * @param array $member_inf 引用返回要添加的数据信息
	 * @param array $extend扩展登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_member_array($group_id, $group_member_inf, &$member_inf, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 构造会员数组信息
		foreach ($group_member_inf as $v) {
			$member_inf[] = array (
				'f_id'         => $group_id,
				'm_uid'        => $v['m_uid'],
				'm_username'   => $v['m_username'],
				'p_sel_mark'   => $this->_d->get_sm_user_check(),
				'p_mark_cd_id' => $this->_d->get_mark_check(),
				'cd_id'        => 0,
				'cd_name'      => '',
				'p_m_type'     => $this->_d->get_mt_collaborators(),
				'p_status'     => $this->_d->get_st_create(),
				'p_created'    => NOW_TIME
			);
		}

		return true;
	}

	/**
	 * 添加成员信息
	 * @param array $member_inf 成员信息数组
	 * @param array $extend扩展登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function insert_all_data($member_inf, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 不能为空
		if (!empty($member_inf)) {
			if (!$this->insert_all($member_inf)) {
				$this->_set_error('_ERR_ADD_ERROR');
				return false;
			}

		}

		return true;
	}

	/**
	 * 添加组长信息
	 * @param array $leader_inf 组长信息数组
	 * @param array $extend扩展登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function insert_data($leader_inf, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		if (!$this->insert($leader_inf)) {
			$this->_set_error('_ERR_ADD_ERROR');
			return false;
		}

		return true;
	}

	/**
	 * 分组成员列表
	 * @param int $f_id 分组id
	 * @param string $name 模糊查询关键字
	 * @param array $page_option 分页参数
	 * @param array $order_option 排序参数
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function list_members($f_id, $page_option, $name) {

		// 分组id不能为空
		if (empty($f_id)) {
			$this->_set_error('_ERR_GROUP_ID_IS_NOT_EXIST');
			return false;
		}

		// 判断是name值是否为空
		if (!empty($name)) {
			$order_option = array ('p_id' => 'ASC');

			// 模糊查询
			$data = $this->_d->list_member_by_name($f_id, $page_option, $order_option,$name);
			return $data;
		} else {
			// 根据中文拼音字母顺序排序
			$order_option = "convert(`m_username` using gbk) asc";

			// 正常查询
			$data = $this->_d->list_member_by_fid($f_id, $page_option, $order_option);
			return $data;
		}

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

		// 分组id不能为空
		if (empty($f_id)) {
			$this->_set_error('_ERR_GROUP_ID_IS_NOT_EXIST');
			return false;
		}

		return $this->_d->delete_member($f_id, $m_uid);
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

		return $this->_d->list_groups($m_uid, $page_option, $order_option);
	}

	/**
	 * 获取存入权限表中的部门id
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_depart_id() {

		return $this->_d->list_depart_id();
	}

	/**
	 * 获取部门成员列表
	 * @param array $depart_id 部门id数组
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_depart_member($depart_ids) {

		return $this->_d->list_depart_member($depart_ids);
	}

	/**
	 * 获取权限表中除组长外用户添加的数据
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_all_member_by_user() {

		return $this->_d->list_all_member_by_user();
	}

	/**
	 * 比较权限表和成员表中对应部门成员的差异
	 * @param array $data1 权限表中成员id
	 * @param array $data2 成员表id
	 * @param array $member1 引用返回权限表较成员表中多余的id
	 * @param array $member2 引用返回成员表中较权限表多余的id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function compare($data1, $data2, &$member1, &$member2) {

		// 按权限表中信息按照部门分类
		foreach($data1 as $v){

			$temp[$v['cd_id']][]=$v;
		}

		// 信息按照部门比对
		foreach ($temp as $k => $v) {
			// 权限表中获取m_uid
			$q_m_uids = array_column($v, 'm_uid');

			// 成员表中获取m_uid
			$m_m_uids = array_column($data2[$k], 'm_uid');

			// 获取权限表较成员表中多余的id
			$val = array_diff($q_m_uids, $m_m_uids);

			// 判断权限表较成员表中多余的id是否为空
			if (!empty($val)) {
				$member1[$k] = $val;
			}

			// 获取成员表中较权限表多余的id
			$val = array_diff($m_m_uids, $q_m_uids);

			// 判断成员表中较权限表多余的id是否为空
			if (!empty($val)) {
				$member2[$k] = $val;
			}

		}
		
		return true;
	}

	/**
	 * 删除权限表中存在而部门表中已删除的成员
	 * @param array $member1 权限表中要删除的成员id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_uids($member1) {

		foreach ($member1 as $k => $v) {
			$this->get_delete_by_uid($k, $v);
		}

		return true;
	}

	/**
	 * 根据部门id和成员id删除权限表中的成员
	 * @param int $cd_id 部门id
	 * @param array $m_uids 成员id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_delete_by_uid($cd_id, $m_uids) {

			$this->_d->delete_by_uid($cd_id, $m_uids);
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

		return $this->_d->delete_by_cd_id($cdids, $fid);
	}

	/**
	 * 删除权限表中的分组下的某成员
	 * @param array $muids 成员id
	 * @param int $fid 分组id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_by_m_uid($muids, $fid) {

		return $this->_d->delete_by_m_uid($muids, $fid);
	}


	/**
	 * 获取成员表中新增信息
	 * @param array $member2 成员表较权限表新增的成员信息
	 * @param array $data2 对应成员表信息
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_group_member($member2, $data2) {

		// 读取部门信息缓存信息
		$cache = &Cache::instance();
		$department_list = $cache->get('Common.department');

		// 遍历新增新增成员信息
		foreach ($member2 as $k => $v) {

			// 获取新增成员部门信息
			$depart_inf = $this->_d->get_depart_inf($k, $department_list);

			// 查找包含该部门的分组
			$group_inf = $this->_d->list_group($k);

			$this->list_group_member_bl($group_inf, $depart_inf, $v, $data2[$k], $return_data);
		}

		// 返回数据不为空插入
		if (!empty($return_data)) {

			// 插入获取数据
			if (!$this->insert_all($return_data)) {
				$this->_set_error('_ERR_ADD_ERROR');
				return false;
			}

		}

		return true;
	}

	/**
	 * 遍历获取成员信息
	 * @param array $group_inf 分组信息
	 * @param array $depart_inf 部门信息
	 * @param array $member_ids 成员id数组
	 * @param array $data 成员表信息
	 * @param array $return_data 成员表信息
	 * @return array 传引用返回值
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_group_member_bl($group_inf, $depart_inf, $member_ids, $data, &$return_data) {

		// 遍历部门下成员
		foreach ($member_ids as $member_id) {

			// 获取成员信息
			$member_inf = $this->get_member_inf($member_id, $data);

			// 遍历分组，组织信息
			foreach ($group_inf as $inf) {
				$this->list_group_member_inf($inf, $depart_inf, $member_inf, $return_data);
			}

		}

		return true;
	}

	/**
	 * 根据成员id获取成员信息
	 * @param int $m_uid
	 * @param array $member_inf 成员信息
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_member_inf($m_uid, $member_inf) {

		// 遍历成员信息
		foreach ($member_inf as $inf) {

			// 获取指定成员信息
			if ($inf['m_uid'] == $m_uid) {
				return array (
					'm_uid'      => $inf['m_uid'],
					'm_username' => $inf['m_username']
				);
			}

		}

		return true;
	}

	/**
	 * 组织插入权限表的信息
	 * @param array $inf 分组信息
	 * @param array $depart_inf 部门信息
	 * @param array $member_id 成员id
	 * @param array $return_data 成员表信息
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_group_member_inf($group_inf, $depart_inf, $member_inf, &$return_data) {

		$return_data[] = array (
			'f_id'         => $group_inf['f_id'],
			'm_uid'        => $member_inf['m_uid'],
			'm_username'   => $member_inf['m_username'],
			'p_sel_mark'   => $this->_d->get_sm_logic(),
			'p_mark_cd_id' => $group_inf['p_mark_cd_id'],
			'cd_id'        => $depart_inf['cd_id'],
			'cd_name'      => $depart_inf['cd_name'],
			'p_m_type'     => $this->_d->get_mt_collaborators(),
			'p_status'     => $this->_d->get_st_update(),
			'p_updated'    => NOW_TIME
		);
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

		return $this->_d->delete_by_depart($cd_ids);
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

		return $this->_d->delete_member_by_id($member_ids);
	}

	/**
	 * 获取权限表中用户添加数据和成员表中差异
	 * @param array $data0 权限表中用户添加的部分信息
	 * @param array $all_member 成员表信息
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function list_diff_member_id($data0, $all_member) {

		$q_m_uids = array_column($data0, 'm_uid');
		$m_m_uids = array_column($all_member, 'm_uid');

        // 返回差异
		return array_diff($q_m_uids, $m_m_uids);
		
	}

}
