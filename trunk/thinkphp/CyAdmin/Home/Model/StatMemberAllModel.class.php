<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Home\Model;

class StatMemberAllModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/*
	 * 获取昨日数据
	 * @param $epid 公司id
	 * @param $cond 过滤时间
	 * return array
	 */
	public function list_by_conds_lastlay($ep_id,$cond){

		$where = array(
			'ep_id = ?',
			'time > ?',
			'status < ?'
		);

		$where_params = array(
			$ep_id,
			$cond,
			$this->get_st_delete()
		);

		$sql = "SELECT a.*, b.active_count FROM  TABLE AS a LEFT JOIN cy_stat_active AS b ON a.ep_id = b.ep_id WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_array($sql,$where_params);
	}

	/*
	 * 获取数据详情
	 */
	public function list_by_conds_detail($ep_id,$page_option){

		$where = array(
			'ep_id = ?',
			'status < ?'
		);

		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$sql = "SELECT a.*, b.active_count FROM  TABLE AS a LEFT JOIN cy_stat_active AS b ON a.ep_id = b.ep_id WHERE ".implode(' AND ',$where)."{$orderby}{$limit}";
		return $this->_m->fetch_array($sql, $where_params);
	}

}