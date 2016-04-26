<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:50
 */

namespace OaRpc\Service;

class CompanyPaysettingService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("CompanyPaysetting");
	}

	/**
	 * 根据epid 获取数据集
	 * @param $epid
	 * @return mixed
	 */
	public function list_by_epid($epid) {

		 return $this->_d->list_by_conds(array('ep_id' => $epid));
	}

}