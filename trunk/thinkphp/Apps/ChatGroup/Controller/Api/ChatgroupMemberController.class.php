<?php
/**
 * ChatgroupMemberController.class.php
 * $author$
 */

namespace ChatGroup\Controller\Api;

class ChatgroupMemberController extends AbstractController {

	/**
	 * 获取消息列表
	 * @return array 消息列表
	 */
	public function  Get_record_list_get() {

		// 查询条件
		$cg_name = I('get.cg_name');
		// 每页条数
		$limit = (int)I('get.limit');
		$page = I('get.page');

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if ($limit < cfg('PERPAGE_MIN') || $limit > cfg('PERPAGE_MAX')) {
			$limit = $this->_plugin->setting['perpage'];
		}
		list($start, $limit, $page) = page_limit($page, $limit);
		$serv_groupmember = D('ChatGroup/ChatgroupMember', 'Service');
		// 分页参数
		$page_option = array(
			$start,
			$limit
		);
		$chatgroups = array();
		$m_uid = $this->_login->user['m_uid'];
		// 列表总数
		$count = $serv_groupmember->count_by_uid_cgname($m_uid, $cg_name);

		// 消息列表
		$chatgroups = $serv_groupmember->list_by_uid_cgname($m_uid, $cg_name, $page_option);

		// 添加用户头像字段
		if (!empty($chatgroups)) {
			$serv_group = D('ChatGroup/Chatgroup', 'Service');
			$chatgroups = $serv_group->get_face_by_cgids($m_uid, $chatgroups);
		}
		// 组合返回数组
		$res = array(
			"total" => $count,
			"limit" => $limit,
			"chatgroups" => $chatgroups,
		);

		$this->_response($res);
	}

	/**
	 * 获取未读消息列表
	 *
	 * @return array 未读消息列表
	 */
	public function Get_unread_list_get() {

		// 每页条数
		$limit = (int)I('get.limit');

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if ($limit < cfg('PERPAGE_MIN') || $limit > cfg('PERPAGE_MAX')) {
			$limit = $this->_plugin->setting['perpage'];
		}

		$serv_groupmember = D('ChatGroup/ChatgroupMember', 'Service');

		// 消息列表
		$chatgroups = array();
		$m_uid = $this->_login->user['m_uid'];

		// 列表总数
		$count = $serv_groupmember->count_by_uid_cgname($m_uid);

		// 未读消息列表列表
		$chatgroups = $serv_groupmember->list_unread_by_uid($m_uid, array(0, $limit));

		// 添加用户头像字段
		if (!empty($chatgroups)) {
			$serv_group = D('ChatGroup/Chatgroup', 'Service');
			$chatgroups = $serv_group->get_face_by_cgids($m_uid, $chatgroups);
		}
		// 组合返回数组
		$res = array(
			"total" => $count,
			"limit" => $limit,
			"chatgroups" => $chatgroups,
		);
		$this->_response($res);
	}
}
