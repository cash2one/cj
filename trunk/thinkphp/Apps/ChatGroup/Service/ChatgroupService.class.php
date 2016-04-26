<?php

/**
 * ChatgroupService.class.php
 * $author$
 */
namespace ChatGroup\Service;

use Common\Common\Login;
use ChatGroup\Model;
use ChatGroup;
use Common\Common\User;

class ChatgroupService extends AbstractService {

	// ChatgroupMember 实例
	protected $_cgm_d;
	// ??
	protected $_com_member_d;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("ChatGroup/Chatgroup");
		$this->_cgm_d = D("ChatGroup/ChatgroupMember");
	}

	/**
	 * 根据 chatid 读取群组信息
	 * @param string $chatid 微信群组id
	 */
	public function get_by_chatid($chatid) {

		return $this->_d->get_by_chatid($chatid);
	}

	/**
	 * 新增聊天群组操作
	 *
	 * @param array &$chatgroup 群组信息
	 * @param array $params 传入的参数
	 * @param mixed $extend 扩展参数
	 */
	public function create_chat_group(&$chatgroup, &$userlist, $params, $extend = array()) {

		// 获取入库参数
		$uid = (string)$extend['uid'];
		$username = (string)$extend['username'];
		$cg_name = (string)$params['cg_name'];
		$m_uids = (array)$params['m_uids'];

		// chatid
		if (empty($params['chatid'])) {
			$chatid = md5(NOW_TIME . random(6));
		} else {
			$chatid = (string)$params['chatid'];
		}

		// 用户信息不能为空
		if (empty($uid) || empty($username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 创建群组名称不能为空
		if (empty($cg_name)) {
			$this->_set_error('_ERR_CHATGROUP_NAME_ERROR');
			return false;
		}

		// 初始化登录用户
		$m_uids[] = $uid;

		// 获取所有聊天群组人员信息
		$members = $this->_list_users($m_uids);

		// 判断群组人员是否为空
		if (1 >= count($members)) {
			$this->_set_error('_ERR_NOT_CHATGROUP_MEMBER_ERROR');
			return false;
		}

		// 拼接用户Id
		$chatgroup_member_array = array();
		$cg_type = $this->_d->get_type_chatgroup();

		// 两条数据，一个群主，最少一个聊天对象
		if (count($members) <= 2) {
			$cg_type = $this->_d->get_type_onechatgroup();
			//获取当前id为群主的群组信息
			$chatgroups = $this->_d->get_chatgroup_by_muid($m_uids[0], 2);
			foreach ($chatgroups as $chatgroup) {
				$chatgroup_members = $this->_cgm_d->list_member_by_cgid($chatgroup['cg_id']);
				foreach ($chatgroup_members as $cg_member) {
					//如果单聊已经有两个人的群组，则返回当前群组信息
					if ($cg_member['m_uid'] == $m_uids[1]) {
						return $chatgroup;
					}
				}
			}

			//获取当前id为群主的群组信息
			$chatgroups = $this->_d->get_chatgroup_by_muid($m_uids[1], 2);
			foreach ($chatgroups as $chatgroup) {
				$chatgroup_members = $this->_cgm_d->list_member_by_cgid($chatgroup['cg_id']);
				foreach ($chatgroup_members as $cg_member) {
					//如果单聊已经有两个人的群组，则返回当前群组信息
					if ($cg_member['m_uid'] == $m_uids[0]) {
						return $chatgroup;
					}
				}
			}

			// 重新整理聊天chatid
			$cg_uids = array();
			foreach ($members as $_m) {
				$cg_uids[] = $_m['m_uid'];
			}

			if (2 > count($cg_uids)) {
				return false;
			}

			$chatid = min($cg_uids[0], $cg_uids[1]);
			$chatid .= '_' . max($cg_uids[0], $cg_uids[1]);
		}

		// 群组信息
		$chatgroup = array(
			'cg_chatid' => $chatid,
			'cg_name' => $cg_name,
			'm_uid' => $uid,
			'm_username' => $username,
			'cg_type' => $cg_type,
			'cg_status' => $this->_d->get_st_create(),
			'cg_created' => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($chatgroup)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		// 获取上面返回群组$id,插入关联表群组人员表
		foreach ($members as $_v) {
			$chatgroup_member_array[] = array(
				'cg_id' => $id,
				'm_uid' => $_v['m_uid'],
				'm_username' => $_v['m_username'],
				'cgm_lasted' => NOW_TIME,
				'cgm_count' => 0,
				'cgm_status' => $this->_d->get_st_create(),
				'cgm_created' => NOW_TIME
			);

			// 如果是当前群主
			if ($uid == $_v['m_uid']) {
				continue;
			}

			$userlist[] = $_v['m_openid'];
		}

		// 执行入库操作
		if (!$this->_cgm_d->insert_all($chatgroup_member_array)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		$chatgroup['cg_id'] = $id;

		return true;
	}

	/**
	 * 退出聊天群组操作
	 *
	 * @param int &$cgid 群组id
	 * @param int $uid 用户uid
	 */
	public function quit_chat_group(&$cgid, $uid) {

		// 获取入库参数
		$cgid = (int)$cgid;
		$uid = (int)$uid;

		// 群组id和退出群组用户id都不能为空
		if (empty($cgid) || empty($uid)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 判断是否为群主
		if ($this->is_creater($uid, $cgid)) {
			$this->_set_error('_ERR_CREATER_CANNOT_QUIT');
			return false;
		}

		// 执行入库操作
		if (!$this->_cgm_d->quit_chatgroup($cgid, $uid)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		return true;
	}


	/**
	 * 检查用户是否为创建者
	 * @param int $uid 用户ID
	 * @param int $cgid 群组ID
	 * @return boolean
	 */
	public function is_creater($uid, $cgid) {

		return $this->_d->is_creater($uid, $cgid);
	}

	/**
	 * 根据群组id获得聊天组信息
	 * @param int $cg_id 群组d
	 * @return array 群组信息
	 */
	public function get_chatgroup_by_cgid($cg_id) {

		$chatgroup = $this->_d->get_chatgroup_by_cgid($cg_id);

		// 如果群组不存在提示错误信息
		if (empty($chatgroup)) {
			$this->_set_error('_ERR_EDIT_GROUP_NOEXIST');
			return false;
		}

		return $chatgroup;
	}

	/**
	 * 编辑聊天群组信息
	 * @param int $cgid 聊天组ID
	 * @param string $cg_name 聊天组名称
	 */
	public function edit_by_cgid($cgid, $cg_name) {

		// 聊天群组ID 不能为空
		if (empty($cgid) && empty($cg_name)) {
			$this->_set_error('_ERR_GROUP_MESSAGE');
			return false;
		}

		// 参数
		$params = array(
			'cg_name' => $cg_name,
			'cg_status' => $this->_d->get_st_update(),
			'cg_updated' => NOW_TIME,
		);

		// 如果执行出错
		if (!$this->update($cgid, $params)) {
			$this->_set_error('_ERR_EDIT_GROUP_MESSAGE');
			return false;
		}

		return true;
	}

	/**
	 * 根据群组id返回群组头像
	 * @param $array 消息列表
	 * retunrn array 群组头像集合
	 */
	public function  get_face_by_cgids($uid, $serv_groupmember) {

		$cgid2uid = array();
		$cgids = array();
		// 获取群组id集合
		foreach ($serv_groupmember as $key => $value) {
			//array_push($ids, $value['cg_id']);
			if ($value['cg_type'] == $this->_d->get_type_chatgroup()) {
				$cgid2uid[$value['cg_id']] = $value['m_uid'];
			} else {
				$cgids[] = $value['cg_id'];
			}
		}

		// 获取应该获取头像的用户uid
		if (!empty($cgids)) {
			$uidarray = $this->_d->get_uids_by_cgids($uid, $cgids);
			//$uids = array();
			//获取用户头像uid集合
			foreach ($uidarray as $key => $value) {
				//array_push($uids, $value['m_uid']);
				$cgid2uid[$value['cg_id']] = $value['m_uid'];
			}
		}

		// 获取用户头像
		$face = array();
		$g_name = array();
		foreach ($this->_d->get_chatface_by_cgids($cgid2uid) as $key => $val) {
			$face[$val["m_uid"]] = User::instance()->avatar(0, $val);
			$g_name[$val["m_uid"]] = $val["m_username"];
		}

		foreach ($serv_groupmember as $_key => $_val) {
			$serv_groupmember[$_key]['m_face'] = $face[$cgid2uid[$_val['cg_id']]];
			if ($serv_groupmember[$key]['cg_type'] == $this->_d->get_type_onechatgroup()) {
				$serv_groupmember[$key]['cg_name'] = $g_name[$cgid2uid[$_val['cg_id']]];
			}
		}

		return $serv_groupmember;
	}

}
