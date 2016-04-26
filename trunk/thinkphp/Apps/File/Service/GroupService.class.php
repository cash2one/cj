<?php
/**
 * GroupService.class.php
 * @create-time: 2015-07-02
 */
namespace File\Service;

class GroupService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 实例化分组权限
		$this->_d = D("File/File");
	}

	/**
	 * 根据传入值判断是否为分组
	 * @param int $fid 分组id
	 * @return bool
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function is_group($fid) {

		// 获取分组id
		$f_id = (int)$fid;

		// 获取分组信息
		$f_data = $this->get($f_id);
		if (empty($f_data) || $f_data['f_level'] != $this->_d->get_f_group()) {
			return false;
		}
		
		return true;
	}

	/**
	 * 删除分组id信息
	 * @param int $params 分组id
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all($fid) {

		// 获取分组id
		$f_id = (int)$fid;

		// 删除对应分组成员
		if (!$this->_d->delete_all($f_id)) {
			return false;
		}

		return true;
	}

	/**
	 * 插入分组信息
	 * @param array $group_inf 引用返回信息
	 * @param array $params 提交分组信息
	 * $extend 用户登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function add_group(&$group_inf, $params, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];
		$group_name = (string)$params['group_name'];
		$group_description = (string)$params['group_description'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 分组名称不能为空
		if (empty($group_name)) {
			$this->_set_error('_ERR_GROUP_NAME');
			return false;
		}

		// 判断分组名称是否已存在
		if (!$this->veryfy($group_name)) {
			$this->_set_error('_ERR_GROUP_NAME_ERROR');
			return false;
		}

		// 分组信息
		$group_inf = array (
			'm_uid'         => $m_uid,
			'm_username'    => $m_username,
			'f_name'        => $group_name,
			'f_description' => $group_description,
			'f_level'       => $this->_d->get_f_group(),
			'f_status'      => $this->_d->get_st_create(),
			'f_created'     => NOW_TIME,
		);

		// 执行入库操作
		if (!$id = $this->insert($group_inf)) {
			E(L('_ERR_INSERT_ERROR'));
			return false;
		}

		// 返回id
		$group_inf['f_id'] = $id;

		// 更改group_id的参数值
		$group_id = array (group_id => $id);
		if (!$this->update($id, $group_id)) {
			E(L('_ERR_update_ERROR'));
			return false;
		}
		
		return true;
	}

	/**
	 * 判断分组名称是否有效
	 * @param array $group_name 引用返回信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function veryfy($group_name) {

		return $this->_d->veryfy($group_name);
	}

	/**
	 * 编辑分组信息
	 * @param array $params 引用返回数组
	 * @param array $params 传入分组信息
	 * @param array $extend 扩展用户登录信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function edit_group(&$group_inf, $params, $extend = array ()) {

		// 获取入库参数
		$m_uid = (int)$extend['m_uid'];
		$m_username = (string)$extend['m_username'];
		$group_id = (int)$params['group_id'];
		$group_name = (string)$params['group_name'];
		$group_description = (string)$params['group_description'];

		// 用户信息不能为空
		if (empty($m_uid) || empty($m_username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 分组名称不能为空
		if (empty($group_name)) {
			$this->_set_error('_ERR_GROUP_NAME');
			return false;
		}

		// 分组信息
		$group_inf = array (
			'f_name'        => $group_name,
			'f_description' => $group_description,
			'f_status'      => $this->_d->get_st_update(),
			'f_updated'     => NOW_TIME
		);

		// 执行入库操作
		if (!$this->update($group_id, $group_inf)) {
			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}
}
