<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Home\Service;

class StatCompanyService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Home/StatCompany');
	}

	/**
	 * 读取所有公司数据
	 * @param $params array 参数
	 * @return mixed
	 */
	public function list_by_conds_cp($params) {

		return $this->_d->list_by_conds_cp($params);
	}
}