<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:53
 */

namespace OaRpc\Model;

class EnterpriseMessageLogModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/***
	 * @description 查询数据结果数量
	 * @param $ep_id
	 * @param $re_array
	 * @return array
	 */
	public function get_real_count($ep_id, $re_array) {

		$sql = "SELECT COUNT(*) AS COUNT FROM __TABLE__";
		// 搜索条件
		$where = array(
			'epid IN (?)',
			'logid NOT IN (?)',
			'status < ?'
		);
		//  做下兼容性处理
		if (empty($re_array)) {
			$re_array[0] = 0;
		}

		$where_params = array(
			array($ep_id, 0),
			$re_array,
			$this->get_st_delete()
		);

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/***
	 * @description 查询消息已读结果数量
	 * @param $ep_id
	 * @param $re_array
	 * @return array
	 */
	public function get_yd_count($ep_id, $re_array) {

		$sql = "SELECT COUNT(*) AS COUNT FROM __TABLE__";
		// 搜索条件
		$where = array(
			'epid IN (?)',
			'logid IN (?)',
			'status < ?'
		);
		//  做下兼容性处理
		if (empty($re_array)) {
			$re_array[0] = 0;
		}

		$where_params = array(
			array($ep_id, 0),
			$re_array,
			$this->get_st_delete()
		);

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}



	/**
	 * 获取列表啊
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @param $orderby
	 * @return array
	 */
	public function get_real_list($ep_id, $re_array, $page_start, $limit, $orderby) {

		$sql = "SELECT * FROM __TABLE__";
		// 搜索条件
		$where = array(
			'epid IN (?)',
			'logid NOT IN (?)',
			'status < ?'
		);
		//  做下兼容性处理
		if (empty($re_array)) {
			$re_array[0] = 0;
		}
		$where_params = array(
			array($ep_id, 0),
			$re_array,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY `logid` DESC  LIMIT ' . $limit . ' OFFSET ' . $page_start , $where_params);

		// return $this->_m->getLastSql();
	}


	/**
	 * 获取已读列表
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @param $orderby
	 * @return array
	 */
	public function get_old_list($ep_id, $re_array, $page_start, $limit) {

		$sql = "SELECT * FROM __TABLE__";
		// 搜索条件
		$where = array(
			'epid IN (?)',
			'logid IN (?)',
			'status < ?'
		);
		//  做下兼容性处理
		if (empty($re_array)) {
			$re_array[0] = 0;
		}
		$where_params = array(
			array($ep_id, 0),
			$re_array,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY `logid` DESC  LIMIT ' . $limit . ' OFFSET ' . $page_start , $where_params);

		// return $this->_m->getLastSql();
	}


}