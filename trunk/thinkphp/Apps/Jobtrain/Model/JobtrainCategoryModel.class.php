<?php
namespace Jobtrain\Model;

class JobtrainCategoryModel extends AbstractModel {

	// 构造方法
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 根据权限获取分类树
	 * @param int $m_uid
	 * @param arr $cd_ids
	 * @return array
	 */
	public function get_tree_with_right($m_uid, $cd_ids) {
		$sql = "SELECT a.title, a.id, a.pid, a.article_num FROM __TABLE__ a LEFT JOIN oa_jobtrain_right b ON a.id=b.cid WHERE (b.is_all=1 OR b.m_uid=? OR b.cd_id IN(?)) AND a.is_open=1 AND a.status<? ORDER BY a.orderid ASC, a.id ASC";
		$params = array($m_uid, $cd_ids, $this->get_st_delete());
		$result = $this->_m->fetch_array($sql, $params);
		return $this->_get_tree($result);
	}

	/**
	 * 递归输出分类树
	 * @param arr $data
	 * @param int $id
	 * @return array
	 */
	private function _get_tree($data, $id=0) {
		$arr = array();
		foreach ( $data as $key => $item ) {
			if($item['pid']==$id){
				$arr[$item['id']]=$item;
				unset($data[$key]);
				$arr[$item['id']]['childs']=$this->_get_tree( $data,$item['id'] );
			}
		}
		return $arr;
	}
}