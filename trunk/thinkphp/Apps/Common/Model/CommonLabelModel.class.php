<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:45
 */
namespace Common\Model;

class CommonLabelModel extends AbstractModel{

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据标签名搜索
	 * @param $params 接收参数
	 * @return array 结果
	 */
	public function list_by_conds_label($params){

		$sql = "SELECT * FROM __TABLE__";
		//搜索条件
		if(!empty($params['name'])){
			$where[] = 'name LIKE ?';
			$where_params[] = '%'.$params['name'].'%';
		}

		$where[] = 'status < (?)';
		$where_params[] = $this->get_st_delete();

		$order_option = array('displayorder' => 'ASC');
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}", $where_params);
	}
}
