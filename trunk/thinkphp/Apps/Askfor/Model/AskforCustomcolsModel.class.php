<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/12
 * Time: 上午11:43
 */

namespace Askfor\Model;

class AskforCustomcolsModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'afcc_';
		parent::__construct();
	}

	/**
	 * 根据aft_id 查询
	 * @param $aft_id
	 * @return array
	 */
	public function list_by_aftid($aft_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'aft_id = ?',
			'afcc_status < ?',
		);
		$where_params = array(
			$aft_id,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

}
