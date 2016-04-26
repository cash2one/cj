<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/24
 * Time: 下午12:30
 */

namespace UcRpc\Controller\Rpc;

class CompanyPaysettingController extends AbstractController {

	/**
	 * 新增企业用户添加试用套件数据
	 * @param $probation_data
	 * @return bool
	 */
	public function insert_probation_data($probation_data) {

		$serv = D('Common/CompanyPaysetting', 'Service');
		$serv->insert_probation_data($probation_data);

		return true;
	}

}
