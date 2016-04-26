<?php
/**
 * ChatgroupMemberService.class.php
 * $author$
 */

namespace ChatGroup\Service;
class ChatgroupMemberService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("ChatGroup/ChatgroupMember");
	}

	/**
	 * 根据群组id获得聊天群组成员信息
	 * @param int $cg_id 群组id
	 * @return array 群组成员
	 */
	public function list_member_by_cgid($cg_id) {

		// 判断用户传递的参数是否为数字
		if ($cg_id < 0) {
			$this->_set_error('_ERR_EDIT_GROUP_CGID');
			return false;
		}

		return $this->_d->list_member_by_cgid($cg_id);
	}

	/**
	 * 根据用户uid获取消息列表
	 * @param int $m_uid 当前用户id
	 * @param string $cg_name 群组名称
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 群组成员
	 */
	public function list_by_uid_cgname($m_uid, $cg_name = '', $page_option, $order_option = array('a.cgm_lasted' => "DESC")) {

		return $this->_d->list_by_uid_cgname($m_uid, $cg_name, $page_option, $order_option);
	}

	/**
	 * 根据用户uid获取未读消息列表
	 *
	 * @param int $m_uid 当前用户uid
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 未读群组消息
	 */
	public function list_unread_by_uid($m_uid, $page_option = array(), $order_option = array()) {

		return $this->_d->list_unread_by_uid($m_uid, $page_option, $order_option);
	}

	/**
	 * 根据用户uid获取消息列表总数
	 * @param int $m_uid 当前用户id
	 * @param string $cg_name 群组名称
	 * @return int 消息总数
	 */
	public function count_by_uid_cgname($m_uid, $cg_name) {

		return $this->_d->count_by_uid_cgname($m_uid, $cg_name);
	}

	/**
	 * 更新未读消息 成功则返回true 否则返回false
	 * @param int $cgid 群组ID
	 * @param int $uid 群成员ID
	 * @param int $step 步长值
	 * @return bool
	 */
	public function increase_unread_count($cgid, $uid, $step = 1) {

		return $this->_d->increase_unread_count($cgid, $uid, $step);
	}

	/**
	 * 重置未读消息数
	 * @param int $group_id 聊天组ID
	 * @param int $u_id ID
	 */
	public function reset_unread_count($group_id, $u_id) {

		return $this->_d->reset_unread_count($group_id, $u_id);
	}

	/**
	 * 添加多个群组成员 成功则返回 true, 否则返回 false
	 * @param int $cgid 群组ID
	 * @param mixed $uids 群成员ID
	 * @return bool
	 */
	public function add_list_members($cgid, $uids) {

		$uids = (array)$uids;

		// 获得存在的用户
		$users = $this->_list_users($uids);

		if (empty($users)) {
			return true;
		}

		// 已经存在的组员
		$exits_members = $this->list_member_by_cgid($cgid);
		$exits_uids = array();
		foreach ($exits_members as $_key => $_val) {
			$exits_uids[] = $_val['m_uid'];
		}

		// 参数
		$params = array();

		// 遍历多个成员
		foreach ($users as $m => $v) {

			// 如果已经是成员，不添加
			if (in_array($v['m_uid'], $exits_uids)) {
				continue;
			}

			$params[] = array(
				'cg_id' => $cgid,
				'm_uid' => $v['m_uid'],
				'm_username' => $v['m_username'],
				'cgm_count' => 0,
				'cgm_lasted' => NOW_TIME,
				'cgm_status' => $this->_d->get_st_create(),
				'cgm_created' => NOW_TIME
			);
		}

		// 如果批量添加失败
		if (!empty($params) && !$this->_d->insert_all($params)) {
			$this->_set_error('_ERR_ADD_MEMBER_MESSAGE');
			return false;
		}

		return true;
	}

	/**
	 * 移除项目组成员
	 * @param int $cgid 项目组ID
	 * @param mixed $uids 项目组成员ID
	 * @return bool 是否移除成功
	 */
	public function remove_member($cgid, $uids) {

		//剔除空值，再判断是否是空
		$uids = array_filter($uids);
		if (empty($uids)) {
			return true;
		}

		// 判断群主是否在删除列表 如果存在就拿出来
		$cg_d = D("ChatGroup/Chatgroup");

		$chatgroup = $cg_d->get_chatgroup_by_cgid($cgid);
		$uid = array_diff($uids, (array) $chatgroup['m_uid']);

		return empty($uid) || $this->_d->remove_member($cgid, $uid);
	}

	/**
	 * 如果用户在群组里, 则返回 true, 否则返回 false
	 * @param mixed $uid 用户ID
	 * @param int $cgid 群组ID
	 * @return boolean
	 */
	public function is_in_chatgroup($uid, $cgid) {

		return $this->_d->is_in_chatgroup($uid, $cgid);
	}

}
