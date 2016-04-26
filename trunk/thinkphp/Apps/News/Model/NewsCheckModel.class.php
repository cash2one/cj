<?php
/**
 * Created by PhpStorm.
 * User: 徐斌山
 * Date: 2015/9/16 0016
 * Time: 17:01
 * Email：
 */
namespace News\Model;

class NewsCheckModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 读取用户预览权限
	 * @param $ne_id
	 * @param $m_uid
	 * @return array
	 */
	public function get_by_conds($ne_id, $m_uid) {

		$sql = "SELECT is_check FROM __TABLE__  WHERE news_id =? AND m_uid=? AND status<? LIMIT 1 ";

		$params = array($ne_id, $m_uid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 根据公告ID  查询审核信息
	 * @param $ne_id
	 * @return array
	 */
	public function get_check_by_ne_id($ne_id, $m_uid) {

		$sql = "SELECT nec_id, is_check, check_note FROM __TABLE__ WHERE news_id=? AND m_uid=? AND status<? LIMIT 1";
		$params = array($ne_id, $m_uid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

}
