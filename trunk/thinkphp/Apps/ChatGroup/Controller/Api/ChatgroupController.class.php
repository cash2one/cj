<?php

/**
 * ChatGroupController.class.php
 * $author$
 */
namespace ChatGroup\Controller\Api;

use Common\Common\Cache;
use Org\Net\Snoopy;
use Think\Exception;
use Common\Service\MemberService;

class ChatgroupController extends AbstractController {


	// 创建聊天群组
	public function Create_group_post() {

		// 聊天群组信息
		$chatgroup = array();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array(
			'uid' => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);

		// 如果新增操作失败
		$serv_cg = D('ChatGroup/Chatgroup', 'Service');
		$userlist = array();
		if (!$serv_cg->create_chat_group($chatgroup, $userlist, $params, $extend)) {
			E($serv_cg->get_errcode() . ':' . $serv_cg->get_errmsg());
			return false;
		}

		// 创建企业号群聊
		$userlist[] = $this->_login->user['m_openid'];
		$serv_cg->wxqy_create($chatgroup['cg_chatid'], $chatgroup['cg_name'], $this->_login->user['m_openid'], $userlist);

		// 格式化
		$serv_fmt = D('ChatGroup/Format', 'Service');
		$serv_fmt->chatgroup_format($chatgroup);

		$this->_result = $chatgroup;
		return true;
	}

	// 退出聊天群组
	public function Quit_group_post() {

		// 聊天群组信息
		$cgid = I('put.cg_id');
		$uid = $this->_login->user['m_uid'];
		// 判断是否在群组里
		if (!$this->_is_in_chatgroup($uid, $cgid)) {
			E('_ERR_EXIT_OR_NOT_IN_CHATGROUP');
			return false;
		}

		// 如果退出操作失败
		$serv_cg = D('ChatGroup/Chatgroup', 'Service');
		if (!$serv_cg->quit_chat_group($cgid, $uid)) {
			E($serv_cg->get_errcode() . ':' . $serv_cg->get_errmsg());
			return false;
		}

		// 退出企业号群聊
		$chatgroup = $serv_cg->get($cgid);
		$serv_cg->wxqy_quit($chatgroup['cg_chatid'], $this->_login->user['m_openid']);

		$this->_result = $cgid;

		return true;
	}

	/**
	 * 获得聊天组信息
	 * @return array 群组信息
	 */
	public function  Get_chatgroup_get() {

		// 前台提交的群组id
		$cg_id = (int)I('get.cg_id');

		// 判断当前用户是否属于组内成员
		if (!$this->_is_in_chatgroup($this->_login->user['m_uid'], $cg_id)) {
			$this->_set_error('_ERR_NOT_MEMBER_MESSAGE');
			return false;
		}

		$chatgroup = array();
		$serv_group = D('ChatGroup/Chatgroup', 'Service');
		$serv_groupmember = D('ChatGroup/ChatgroupMember', 'Service');

		// 聊天群组信息
		if (!$chatgroup = $serv_group->get_chatgroup_by_cgid($cg_id)) {
			$this->_set_error('_ERR_EDIT_GROUP_NOEXIST');
			return false;
		}

		// 聊天组成员
		$chatgroup['members'] = array();
		if (!$members = $serv_groupmember->list_member_by_cgid($cg_id)) {
			$this->_set_error('_ERR_MEMBER_MESSAGE');
			return false;
		}

		$chatgroup['members'] = $members;
		// 格式化
		$serv_fmt = D('ChatGroup/Format', 'Service');
		$serv_fmt->chatgroup_format($chatgroup);
		$this->_response($chatgroup);
	}

	/**
	 * 编辑聊天群组
	 * @return bool 成功|失败
	 */
	public function Edit_post() {

		// 用户提交的参数
		$params = I('request.');
		// 群组ID
		$cg_id = (int)$params['group_id'];
		// 群组名称
		$cg_name = (string)$params['group_name'];
		// 新添加的成员ID
		$new_uids = (array)$params['new_uids'];
		// 移除的成员ID
		$del_uids = (array)$params['del_uids'];
		// 当前用户ID
		$uid = (int)$this->_login->user['m_uid'];

		// 获取群组服务层
		$serv_cg = D('ChatGroup/Chatgroup', 'Service');
		// 获得群成员服务层
		$serv_cgm = D('ChatGroup/ChatgroupMember', 'Service');

		// 判断是否是群主
		if (!$serv_cg->is_creater($uid, $cg_id)) {
			$this->_set_error('_ERR_NOT_CREATER_MESSAGE');
			return false;
		}

		//$group = D('Chatgroup');
		try {
			// 开启事务
			//$group->startTrans();

			// 编辑群组信息
			if (!$serv_cg->edit_by_cgid($cg_id, $cg_name)) {
				E($this->get_errcode() . ":" . $this->get_errmsg());
				return false;
			}

			// 如果有新添加的群组成员
			if (!empty($new_uids)) {
				// 如果添加新成员执行失败
				if (!$serv_cgm->add_list_members($cg_id, $new_uids)) {
					E($this->get_errcode() . ":" . $this->get_errmsg());
					return false;
				}
			}

			// 如果有要删除的聊天组成员
			if (!empty($del_uids)) {
				// 如果移除群成员失败
				if (!$serv_cgm->remove_member($cg_id, $del_uids)) {
					$this->_set_error('_ERR_REMOVE_MEMBER_MESSAGE');
					return false;
				}
			}

			// 编辑微信企业号的群聊信息
			$chatgroup = $serv_cg->get($cg_id);
			$serv_cgm->wxqy_update($chatgroup['cg_chatid'], $cg_name, $this->_login->user['m_openid'], $new_uids, $del_uids);

			// 提交事务
			//$group->commit();

		} catch (\Exception $e) {
			$this->_set_error($e);
			// 出错回滚事务
			//$group->rollback();
			return false;
		}

		return true;
	}
}
