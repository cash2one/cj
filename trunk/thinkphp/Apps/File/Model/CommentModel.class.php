<?php
/**
 * CommentModel.class.php
 * @create-time: 2015-07-02
 */
namespace File\Model;

class CommentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据文件$file_id获取文件评论列表
	 * @param int $file_id 当前文件f_id
	 * @param string $fields 查询字段字符串
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 评论列表
	 */
	public function list_by_fid($file_id, $plugin_id, $page_option = array (), $order_option = array ()) {

		$sql = "SELECT id,m_uid,m_username,reply_id,reply_m_uid,content,created
 				FROM __TABLE__ WHERE obj_id=? AND plugin_id=? AND status<?";

		// 查询条件
		$params = array (
			$file_id,
			$plugin_id,
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
	 * 根据文件$file_id获取文件评论列表总数
	 * @param int $file_id 当前文件f_id
	 * @return int 评论列表总数
	 */
	public function count_by_fid($file_id, $plugin_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ WHERE obj_id=? AND plugin_id=? AND status<?";

		// 查询条件
		$comment_inf = array (
			$file_id,
			$plugin_id,
			$this->get_st_delete()
		);

		return $this->_m->result($sql, $comment_inf);
	}

}
