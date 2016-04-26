<?php
namespace Jobtrain\Model;

class JobtrainArticleModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取文章列表
	 *
	 * @param $cids 分类id
	 * @param $type_id 文章类型
	 * @param $keywords 标题关键字
	 * @param $is_study 是否学习
	 * @param $m_uid 用户id
	 * @return array
	 */
	public function get_list($cids, $type_id, $keywords = '', $is_study = 0, $m_uid, $start, $limit) {

		$where = '';
		$plus = array();
		if ($cids != '') {
			$where .= " AND a.cid IN($cids)";
		} else {
			$where .= " AND a.cid=0";
		}
		if ($type_id !== '') {
			$where .= " AND a.type=?";
			$plus[] = $type_id;
		}
		if ($keywords != '') {
			$where .= " AND (a.title LIKE '%$keywords%' OR a.content LIKE '%$keywords%')";
		}
		if ($is_study == 0) {
			// 全部
			$sql = "SELECT a.type, a.id, a.title, a.cover_id, a.publish_time, a.study_num, b.aid FROM __TABLE__ a LEFT JOIN oa_jobtrain_study b ON a.id=b.aid AND b.m_uid=? AND b.status<? WHERE a.is_publish=1 AND a.status<? $where ORDER BY a.publish_time DESC LIMIT $start,$limit";
			// 合计
			// $tsql = "SELECT COUNT(a.id) FROM __TABLE__ a LEFT JOIN oa_jobtrain_study b ON a.id=b.aid AND b.m_uid=? AND b.status<? WHERE a.is_publish=1 AND a.status<? $where";
			$base = array($m_uid, $this->get_st_delete(), $this->get_st_delete());
		} else if ($is_study == 1) {
			// 已学习
			$sql = "SELECT a.type, a.id, a.title, a.cover_id, a.publish_time, a.study_num, b.aid FROM __TABLE__ a RIGHT JOIN oa_jobtrain_study b ON a.id=b.aid AND b.status<? WHERE a.is_publish=1 AND a.status<? AND b.m_uid=? $where ORDER BY a.publish_time DESC LIMIT $start,$limit";
			// 合计
			// $tsql = "SELECT COUNT(a.id) FROM __TABLE__ a RIGHT JOIN oa_jobtrain_study b ON a.id=b.aid AND b.status<? WHERE a.is_publish=1 AND a.status<? AND b.m_uid=? $where";
			$base = array($this->get_st_delete(), $this->get_st_delete(), $m_uid);
		} else {
			// 未学习
			$sql = "SELECT a.type, a.id, a.title, a.cover_id, a.publish_time, a.study_num FROM __TABLE__ a LEFT JOIN (SELECT aid FROM oa_jobtrain_study WHERE m_uid=? AND status<?) b ON a.id=b.aid WHERE a.is_publish=1 AND a.status<? AND b.aid IS NULL $where ORDER BY a.publish_time DESC LIMIT $start,$limit";
			// 合计
			// $tsql = "SELECT COUNT(a.id) FROM __TABLE__ a LEFT JOIN (SELECT aid FROM oa_jobtrain_study WHERE m_uid=? AND status<?) b ON a.id=b.aid WHERE a.is_publish=1 AND a.status<? AND b.aid IS NULL $where";
			$base = array($m_uid, $this->get_st_delete(), $this->get_st_delete());
		}
		// 合并查询参数
		$params = array_merge($base, $plus);
		$list = $this->_m->fetch_array($sql, $params);
		// $total = $this->_m->result($tsql, $params);
		// return array('list'=>$list,'total'=>$total);
		return array('list' => $list);
	}

	/**
	 * 学习数量+1 成功则返回true 否则返回false
	 *
	 * @param int $id
	 * @return bool
	 */
	public function inc_study_num($id) {

		$sql = "UPDATE __TABLE__ SET study_num=study_num+1, status=?, updated=? WHERE id=? AND status<?";
		// 参数
		$params = array($this->get_st_update(), NOW_TIME, $id, $this->get_st_delete());
		return $this->_m->update($sql, $params);
	}

	/**
	 * 收藏+1
	 *
	 * @param int $id
	 * @return bool
	 */
	public function inc_coll_num($id) {

		$sql = "UPDATE __TABLE__ SET coll_num=coll_num+1, status=?, updated=? WHERE id=? AND status<?";
		// 参数
		$params = array($this->get_st_update(), NOW_TIME, $id, $this->get_st_delete());
		return $this->_m->update($sql, $params);
	}

	/**
	 * 收藏-1
	 *
	 * @param int $id
	 * @return bool
	 */
	public function dec_coll_num($id) {

		$sql = "UPDATE __TABLE__ SET coll_num=coll_num-1, status=?, updated=? WHERE id=? AND status<? AND coll_num>0";
		// 参数
		$params = array($this->get_st_update(), NOW_TIME, $id, $this->get_st_delete());
		return $this->_m->update($sql, $params);
	}

}
