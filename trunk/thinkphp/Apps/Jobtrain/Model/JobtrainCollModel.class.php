<?php
namespace Jobtrain\Model;

class JobtrainCollModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}
	/**
	 * 根据aid和m_uid物理删除
	 * @param int $aid
	 * @param int $m_uid
	 * @return bool
	 */
	public function delete_real_by_aid($aid, $m_uid) {
		$sql = "DELETE FROM __TABLE__ WHERE aid=? AND m_uid=?";
		// 参数
		$params = array($aid, $m_uid);
		return $this->_m->execsql($sql, $params);
	}
	/**
	 * 获取收藏列表
	 * @param $type_id 文章类型
	 * @param $keywords 标题关键字
	 * @param $m_uid 用户id
	 * @return array
	 */
	public function get_list_join_article($type_id, $keywords, $m_uid, $start, $limit) {
		$where = '';
		$plus = array();
		if($type_id!==''){
			$where .= " AND b.type=?";
			$plus[] = $type_id;
		}
		if($m_uid){
			$where .= " AND a.m_uid=?";
			$plus[] = $m_uid;
		}
		if($keywords!=''){
			$where .= " AND (b.title LIKE '%$keywords%' OR b.content LIKE '%$keywords%')";
		}
		$sql = "SELECT b.type, b.id, b.title, b.summary, b.cover_id, b.publish_time, b.study_num FROM __TABLE__ a LEFT JOIN oa_jobtrain_article b ON a.aid=b.id WHERE b.status<? AND b.is_publish=1 AND a.status<? $where ORDER BY b.publish_time DESC LIMIT $start,$limit";
		$base = array( $this->get_st_delete(), $this->get_st_delete());
		$params = array_merge($base, $plus);
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}
}