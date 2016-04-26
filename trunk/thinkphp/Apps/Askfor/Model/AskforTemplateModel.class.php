<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/10
 * Time: 下午2:17
 */
namespace Askfor\Model;

class AskforTemplateModel extends AbstractModel {

	const ISUSE = 1;//开启模板
	const NOUSE = 0;//关闭模板
	// 构造方法
	public function __construct() {

		$this->prefield = 'aft_';
		parent::__construct();
	}

	/**
	 * 根据aft_id 查询
	 * @param $aft_id
	 * @return array
	 */
	public function get_by_aft_id($aft_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'aft_id = ?',
			'aft_status < ?',
		);
		$where_params = array(
			$aft_id,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据orderid排序
	 * @param $order_option 排序条件
	 * @return array|bool
	 */
	public function list_all_orderby_orderid($order_option) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'aft_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}", $where_params);
	}
}
