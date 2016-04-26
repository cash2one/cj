<?php
/**
 * CommentModel.class.php
 * $author$
 */
namespace PubApi\Model;

class CommentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据应用id和评论对象id获取总数
	 * @param int $obj_id 评论对象id
	 * @param int $identifier 应用唯一标识名
	 * @return array
	 */
	public function count_by_objid_pluginid($obj_id, $identifier) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ WHERE obj_id=? AND cp_identifier=? AND status<?";

		// 查询条件
		$params = array (
			$obj_id,
			$identifier,
			$this->get_st_delete()
		);
		return $this->_m->result($sql, $params);
	}

	/**
	 * 根据uid获取帖子ID
	 * @param int $uid uid
	 * @return array
	 */
	public function list_by_uid($uid) {

		$identifier = "community";
		$sql = "SELECT obj_id FROM __TABLE__ WHERE m_uid=? AND cp_identifier=? AND status<?";

		// 查询条件
		$params = array (
			$uid,
			$identifier,
			$this->get_st_delete()
		);
		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据uid获取帖子ID
	 * @param int $uid uid
	 * @return array
	 */
	public function get_by_id($id) {

		$sql = "SELECT obj_id, cp_identifier FROM __TABLE__ WHERE id=? AND status<? LIMIT 1";

		// 查询条件
		$params = array($id, $this->get_st_delete());
		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 根据应用id和评论对象id获取数据列表
	 * @param int $obj_id 评论对象id
	 * @param int $identifier 应用唯一标识名
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array|bool
	 */
	public function list_by_objid_pluginid($obj_id, $identifier, $page_option = array (), $order_option = array ()) {

		$sql = "SELECT * FROM __TABLE__ WHERE obj_id=? AND cp_identifier=? AND status<?";

		// 查询条件
		$params = array (
			$obj_id,
			$identifier,
			$this->get_st_delete()
		);

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

		return $this->_m->fetch_array($sql."{$order_by}{$limit}", $params);
	}


	/**
	 * 查询公告的作者uid,标题
	 * @param $ne_id
	 * @return array
	 */
	public function get_uid_by_obj_id($obj_id) {

		$sql = "SELECT m_uid, subject FROM __TABLE__ WHERE `obj_id`=? AND `status`<? LIMIT 1";

		$params = array($obj_id, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询用户数据 代替Common操作
	 * @param $m_uid
	 * @return array
	 */
	public function get_by_uid($m_uid) {

		$sql = "SELECT * FROM oa_member WHERE m_uid=? AND m_status<? LIMIT 1";

		$params = array($m_uid, $this->get_st_delete());
		return $this->_m->fetch_row($sql, $params);
	}
}
