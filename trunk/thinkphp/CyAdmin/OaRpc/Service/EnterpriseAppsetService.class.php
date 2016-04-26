<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/27
 * Time: 11:54
 */

namespace OaRpc\Service;

class EnterpriseAppsetService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("EnterpriseAppset");
	}

	/**
	 * 获取总数啊
	 * @return mixed
	 */
	public function list_all() {

		$app_data = $this->_d->list_all();
		$out_app_data = array();
		// 生产所需数据
		foreach ($app_data as $k => $v) {
			if ('trydate' == $v['key']) {
				$out_app_data['trydate'] =$v['value'];
			} elseif ('syq_jjdq_set' == $v['key']) {
				$out_app_data['syq_jjdq_set'] =$v['value'];
			} elseif ('yff_jjdq_set' == $v['key']) {
				$out_app_data['yff_jjdq_set'] =$v['value'];
			}
		}

		return $out_app_data;
	}

}