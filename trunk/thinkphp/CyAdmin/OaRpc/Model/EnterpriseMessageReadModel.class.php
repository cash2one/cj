<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:53
 */

namespace OaRpc\Model;

class EnterpriseMessageReadModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}


	public function list_by_conds($ep_id, $uid) {

		$sql = "SELECT * FROM __TABLE__";
		//搜索条件
		$where = array(
			'uid = ?',
			'status < ?'
		);

		$where_params = array(
			$uid,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}








}