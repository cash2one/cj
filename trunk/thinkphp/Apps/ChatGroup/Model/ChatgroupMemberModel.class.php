<?php

/**
 * ChatgroupMemberModel.class.php
 * $author$
 */
namespace ChatGroup\Model;

class ChatgroupMemberModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cgm_';
	}

	/**
	 * 退出群组
	 * @param int $cg_id 群组ID
	 * @param int $m_uid 用户ID
	 * @return multitype:
	 */
	public function quit_chatgroup($cg_id, $m_uid) {

		return $this->_m->update("UPDATE __TABLE__ SET `cgm_status`=?, `cgm_deleted`=? WHERE `cg_id`=? AND `m_uid`=? AND `cgm_status`<?", array(
			$this->get_st_delete(), NOW_TIME, $cg_id, $m_uid, $this->get_st_delete()
		));
	}

	/**
	 * 根据群组id获得聊天群组成员信息
	 * @param int $cg_id 群组id
	 * @return array 群组成员
	 */
	public function list_member_by_cgid($cg_id) {

		$sql = "SELECT cm.m_uid, m.m_username, m.m_openid, m.m_face FROM __TABLE__ cm LEFT JOIN oa_member m ON cm.m_uid=m.m_uid WHERE `cg_id`=? AND `cgm_status`<?";
		$params = array(
			$cg_id,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据用户uid获取消息列表
	 * @param int $m_uid 当前用户id
	 * @param string $cg_name 群组名称
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 群组成员
	 */
	public function list_by_uid_cgname($m_uid, $cg_name = '', $page_option = array(), $order_option = array()) {

		$sql = "SELECT b.cg_id, b.cg_name, b.m_uid, b.m_username, b.cg_type, b.cg_created, a.cgm_count, a.cgm_lasted FROM __TABLE__ a LEFT JOIN oa_chatgroup b ON a.cg_id=b.cg_id";

		// 条件
		$wheres = array('a.m_uid=? AND a.cgm_status<?');
		$params = array(
			$m_uid,
			$this->get_st_delete()
		);

		// 判断是否为空 如果有群组名称就模糊匹配
		if (!empty($cg_name)) {
			$wheres[] = 'b.cg_name LIKE ?';
			$params[] = '%' . $cg_name . '%';
		}

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

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres) . "{$orderby}{$limit}", $params);
	}

	/**
	 * 根据用户uid获取消息列表总数
	 * @param int $m_uid 当前用户id
	 * @param string $cg_name 群组id
	 * @return int 消息总数
	 */
	public function count_by_uid_cgname($m_uid, $cg_name) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ a LEFT JOIN oa_chatgroup b ON a.cg_id=b.cg_id";
		$params = array(
			$m_uid,
			$this->get_st_delete()
		);
		// 条件
		$wheres = array('a.m_uid=? AND a.cgm_status<?');

		// 如果有群组名称就模糊匹配
		if (!empty($cg_name)) {
			$wheres[] = 'b.cg_name LIKE ?';
			$params[] = '%' . $cg_name . '%';
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $wheres), $params);
	}

	/**
	 * 移除聊天组成员
	 * @param int $cgid 聊天组ID
	 * @param array $uids 要移除的聊天组成员ID(聊天组成员ID)
	 * @return bool
	 */
	public function remove_member($cgid, $uids) {

		$uids = (array)$uids;

		// 更新SQL
		$sql = "UPDATE __TABLE__ SET cgm_status=?, cgm_deleted=? WHERE cg_id=? AND m_uid IN (?) AND cgm_status<?";

		// 参数
		$params = array(
			$this->get_st_delete(),
			NOW_TIME,
			$cgid,
			$uids,
			$this->get_st_delete()
		);

		// 执行移除
		return $this->_m->update($sql, $params);
	}

	/**
	 * 添加未读消息数
	 * @param int $cg_id 聊天组ID
	 * @param int $uid 成员ID
	 * @param int $step 每次要添加的未读消息数
	 * @return bool是否执行成功
	 */
	public function increase_unread_count($cg_id, $uid, $step = 1) {

		$sql = "UPDATE __TABLE__ SET cgm_count=cgm_count+?, cgm_status=?, cgm_lasted=?, cgm_updated=? WHERE cg_id=? AND cgm_status<?";
		// 参数
		$params = array(
			$step,
			$this->get_st_update(),
			NOW_TIME,
			NOW_TIME,
			$cg_id,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $params);
	}

	/**
	 * 重置未读消息数
	 * @param int $cgid 聊天组ID
	 * @param int $uid 成员ID
	 */
	public function reset_unread_count($cgid, $uid) {

		$sql = "UPDATE __TABLE__ SET  cgm_count=0, cgm_status=?, cgm_updated=? WHERE m_uid=? AND cg_id=? AND cgm_status<?";

		// 参数
		$params = array(
			$this->get_st_update(),
			NOW_TIME,
			$uid,
			$cgid,
			$this->get_st_delete()
		);
		return $this->_m->update($sql, $params);
	}

	/**
	 * 如果用户在群组里, 则返回 true, 否则返回 false
	 * @param mixed $uid 用户ID
	 * @param int $cgid 群组ID
	 * @return boolean
	 */
	public function is_in_chatgroup($uid, $cgid) {

		return 0 < $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `m_uid`=? AND `cg_id`=? AND `cgm_status`<?", array(
			$uid, $cgid, $this->get_st_delete()
		));
	}

	/**
	 * 根据用户id获取未读消息列表
	 *
	 * @param int $m_uid 当前用户id
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 未读群组消息
	 */
	public function list_unread_by_uid($m_uid, $page_option = array(), $order_option = array()) {

		$sql = "SELECT b.cg_id, b.cg_name, b.m_uid, b.m_username, b.cg_type, b.cg_created, a.cgm_count, a.cgm_lasted FROM __TABLE__ a LEFT JOIN oa_chatgroup b ON a.cg_id=b.cg_id";

		// 条件
		$wheres = array('a.m_uid=? AND a.cgm_count>0 AND a.cgm_status<?');
		$params = array(
			$m_uid,
			$this->get_st_delete()
		);

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

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres) . "{$orderby}{$limit}", $params);
	}
}
