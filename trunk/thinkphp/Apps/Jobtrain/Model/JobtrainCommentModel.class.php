<?php
namespace Jobtrain\Model;

class JobtrainCommentModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}
	/**
	 * 获取评论列表
	 * @param int $aid
	 * @param int $m_uid
	 * @return bool
	 */
	public function list_by_conds_join_member($aid, $start, $limit) {
		$sql = "SELECT a.id, a.m_uid, a.m_username, a.to_username, a.content, a.zan_num, a.created, b.m_face, c.m_username as r_username, c.content as r_content FROM __TABLE__ a LEFT JOIN oa_member b ON a.m_uid=b.m_uid LEFT JOIN __TABLE__ c ON a.toid=c.id WHERE a.aid=? AND a.status<? ORDER BY a.created DESC LIMIT $start,$limit";
		$params = array($aid, $this->get_st_delete());
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

	/**
	 * 点赞数量+1
	 * @param int $id
	 * @return bool
	 */
	public function inc_zan_num($id) {
		$sql = "UPDATE __TABLE__ SET zan_num=zan_num+1, status=?, updated=? WHERE id=? AND status<?";
		// 参数
		$params = array(
			$this->get_st_update(),
			NOW_TIME,
			$id,
			$this->get_st_delete()
		);
		return $this->_m->update($sql, $params);
	}
	
}