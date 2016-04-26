<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/28
 * Time: 16:11
 */

namespace UcRpc\Service;

class EnterpriseAppsetService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("EnterpriseAppset");
	}

	/**
	 * @return 获取总后台配置数据
	 */
	public function list_all() {

		$app_data = $this->_d->list_all();
		$return_data = array();
		if ($app_data) {
			foreach ($app_data as $k => $v) {
				if ('syq_jjdq_set' == $v['key']) {
					$return_data['syq_jjdq_set'] = $v['value'];
				}

				if ('trydate' == $v['key']) {
					$return_data['trydate'] = $v['value'];
				}

				if ('yff_jjdq_set' == $v['key']) {
					$return_data['yff_jjdq_set'] = $v['value'];
				}
			}
		}

		return $return_data;
	}

}