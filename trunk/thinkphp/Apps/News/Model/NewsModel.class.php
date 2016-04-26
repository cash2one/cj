<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/9/11
 * Time: 10:24
 */

namespace News\Model;

class NewsModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据公告IDS 查询多条公告列表
	 * @param $ne_ids
	 * @param string $keywords
	 * @param array $page_option
	 * @return array
	 */
	public function list_by_ne_id($ne_ids, $keywords = '', $page_option = array()) {

		// 判断当前是否分页
		$limit = '';
		if ($page_option) {
			$limit = " LIMIT ".$page_option[0].','.$page_option[1];
		}

		// 判断当前关键字是否为空
		$where = '';
		if (!empty($keywords)) {
			$where = ' AND title LIKE \'%'.$keywords.'%\'';
		}

		$sql = "SELECT ne_id, title, published, is_publish FROM __TABLE__ WHERE `".$this->_m->getPk()."` IN (?) AND status<? AND is_publish = 1 {$where} ORDER BY published DESC{$limit}";

		$params = array($ne_ids, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 获取分类下的草稿
	 * @param $m_uid
	 * @param $nca_id
	 * @param $keyword
	 * @return array
	 */
	public function list_by_ne_id_check($m_uid, $nca_id, $keyword) {

		// 判断当前关键字是否为空
		$where = '';
		if (!empty($keyword)) {
			$where = ' AND title LIKE \'%'.$keyword.'%\'';
		}

		$sql = "SELECT ne_id, title, is_publish, created FROM __TABLE__ WHERE m_uid = {$m_uid} AND status<? AND nca_id = {$nca_id} AND is_check = 1 {$where} ORDER BY created DESC";

		$params = array($this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 筛选条件下的记录数
	 * @param $ne_ids
	 * @param string $keywords
	 * @return array
	 */
	public function count_by_ne_id($ne_ids, $keywords = '') {

		// 判断当前关键字是否为空
		$where = '';
		if (!empty($keywords)) {
			$where = ' AND title LIKE \'%'.$keywords.'%\'';
		}

		$sql = "SELECT count(*) as total FROM __TABLE__ WHERE `".$this->_m->getPk()."` IN (?) AND status<? AND is_publish = 1 {$where} ORDER BY published DESC{$limit}";

		$params = array($ne_ids, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询公告详情 选择需要的字段
	 * @param $ne_id 公告ID
	 * @return array
	 */
	public function get_by_ne_id($ne_id, $is_publish) {

		$sql = "SELECT n.ne_id, n.title, n.m_uid, n.is_all, n.is_secret, n.is_comment, n.is_like, n.num_like,n.cover_id, n.summary, n.read_num, n.published, n.nca_id ,n.is_publish,n.is_message, c.content FROM __TABLE__ n LEFT JOIN oa_news_content c on n.ne_id = c.ne_id WHERE n.ne_id =? AND n.status<? AND n.is_publish={$is_publish} LIMIT 1";
		$params = array($ne_id, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询当前用户的部门ID
	 * @param $m_uid
	 * @return array
	 */
	public function get_member_by_uid($m_uid) {

		$sql = "SELECT cd_id FROM `oa_member_department` WHERE `m_uid`=? AND `md_status`<?";
		$params = array($m_uid, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 获取预览详情
	 * @param $ne_id
	 * @return array
	 */
	public function get_preview_by_ne_id($ne_id) {

		$sql = "SELECT ne_id, m_uid, title, is_publish, check_summary FROM __TABLE__ WHERE `ne_id`=? AND `status`<? LIMIT 1";

		$params = array($ne_id, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询公告的作者uid,标题
	 * @param $ne_id
	 * @return array
	 */
	public function get_uid_by_ne_id($ne_id) {

		$sql = "SELECT m_uid, title FROM __TABLE__ WHERE `ne_id`=? AND `status`<? LIMIT 1";

		$params = array($ne_id, $this->get_st_delete());

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

	/**
	 * 获取用户列表
	 * @param $m_uids
	 * @return array
	 */
	public function list_by_uids($m_uids) {

		if (is_array($m_uids)) {
			$uids = implode(',', $m_uids);
		}

		$sql = "SELECT * FROM oa_member WHERE m_uid in ({$uids}) AND m_status<?";

		$params = array($this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}
}
