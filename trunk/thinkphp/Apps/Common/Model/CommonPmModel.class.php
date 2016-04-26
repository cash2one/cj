<?php
/**
 * CommonPmModel.class.php
 * $author$
 */
namespace Common\Model;

class CommonPmModel extends AbstractModel {

	// 是否已读：未读
	const ST_UNREAD = 0;
	// 是否已读：已读
	const ST_ISREAD = 1;

	// 三个月前时间
	protected $_three_months_ago = 0;

	// 获取未读状态
	public function get_st_unread() {

		return self::ST_UNREAD;
	}

	// 获取已读状态
	public function get_st_isread() {

		return self::ST_ISREAD;
	}

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'pm_';

		// 三个月前时间
		$this->_three_months_ago = NOW_TIME - (86400 * 90);
	}

	/**
	 * 根据用户、插件id读取消息总数
	 * @param int $m_uid 用户id
	 * @param int $plugin_id 插件id
	 * @return array
	 */
	public function count_by_uid_pluginid($m_uid, $plugin_id = 0) {

		// 查询sql
		$sql = "SELECT COUNT(*) FROM __TABLE__ ";

		// 条件
		$where = array('`m_uid`=? AND `pm_isread`=? AND `pm_status`<? AND `pm_created`>?');
		$params = array(
			$m_uid,
			$this->get_st_unread(),
			$this->get_st_delete(),
			$this->_three_months_ago
		);

		// 插件id不为空，拼接查询条件
		if ($plugin_id != 0) {
			$where[] = '`cp_pluginid`=?';
			$params[] = $plugin_id;
		}

		// 返回结果
		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $params);
	}

	/**
	 * 根据用户、插件id、内容关键字获取消息总数
	 * @param int $m_uid 用户id
	 * @param int $plugin_id 插件id
	 * @param string $msg 消息内容关键字
	 * @return array
	 */
	public function count_by_msg_pid($m_uid, $plugin_id = 0, $msg = '') {

		// 查询sql
		$sql = "SELECT COUNT(*) FROM __TABLE__ ";

		// 条件
		$where = array('`m_uid`=? AND `pm_status`<? AND `pm_created`>?');
		$params = array(
			$m_uid,
			$this->get_st_delete(),
			$this->_three_months_ago
		);

		// 插件id不为空，拼接查询条件
		if ($plugin_id != 0) {
			$where[] = '`cp_pluginid`=?';
			$params[] = $plugin_id;
		}

		// 查询内容不为空，拼接查询条件
		if (!empty($msg)){
			$where[] = '`pm_message` LIKE ?';
			$params[] = '%' . $msg . '%';
		}

		// 返回结果
		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $params);
	}

	/**
	 * 根据用户、插件id、内容关键字获取消息列表
	 * @param int $m_uid 用户id
	 * @param int $plugin_id 插件id
	 * @param string $msg 消息内容关键字
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array|bool
	 */
	public function list_by_msg_pid($m_uid, $plugin_id = 0, $msg = '', $page_option = array(), $order_option = array()) {

		// 查询sql
		$sql = "SELECT `pm_id`, `cp_pluginid`, `m_uid`, `m_username`, `from_uid`, `from_username`, `pm_title`, `pm_message`, `pm_params`, `pm_isread`, `pm_created` FROM __TABLE__";
		// 条件
		$where = array('`m_uid`=? AND `pm_status`<? AND `pm_created`>?');

		$params = array(
			$m_uid,
			$this->get_st_delete(),
			$this->_three_months_ago
		);

		// 插件id不为空，拼接查询条件
		if ($plugin_id != 0) {
			$where[] = '`cp_pluginid`=?';
			$params[] = $plugin_id;
		}

		// 查询内容不为空，拼接查询条件
		if (!empty($msg)) {
			$where[] = 'pm_message LIKE ?';
			$params[] = '%' . $msg . '%';
		}

		// 排序
		$order_by = '';
		if (!$this->_order_by($order_by, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 返回结果
		return $this->_m->fetch_array($sql." WHERE ".implode(' AND ', $where)."{$order_by}{$limit}", $params);
	}

	/**
	 * 根据记录id标记已/未读状态
	 * @param array $pm_ids 记录ids
	 * @param int $isread 是否已读：0=未读，1=已读
	 * @return bool
	 */
	public function update_isread_by_ids($pm_ids, $isread) {

		// 更新sql语句
		$sql = "UPDATE __TABLE__ SET `pm_isread`=? ,`pm_status`=? ,`pm_updated`=? WHERE `pm_id` IN (?) AND `pm_status`<? AND `pm_created`>?";

		// 更新条件参数
		$params = array(
			$isread,
			$this->get_st_update(),
			NOW_TIME,
			$pm_ids,
			$this->get_st_delete(),
			$this->_three_months_ago
		);

		// 执行入库操作
		if (!$this->_m->update($sql, $params)) {
			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}
}
