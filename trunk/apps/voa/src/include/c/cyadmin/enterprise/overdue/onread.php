<?php
/**
 * @Author: ppker
 * @Date:   2015-10-19 18:07:02
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-20 20:32:48
 */

class voa_c_cyadmin_enterprise_overdue_onread extends voa_c_cyadmin_enterprise_base {

	public function execute() {

		$read_data = $this->request->post('read'); // 已读数组
		$uid = $this->request->post('uid'); // 后台的登陆ca_id

		$ovid = $this->request->get('ovid'); // 已读单个

		$serv = &service::factory( 'voa_s_cyadmin_enterprise_dueread' );

		// make data insert_multi
		$insert_data = array();
		if (!is_array($read_data)) {
			$read_data = array($read_data);
		}

		if (!empty($read_data)) {
			foreach ($read_data as $k => $val) {
				$insert_data[$k]['uid'] =  $uid;
				$insert_data[$k]['ovid'] = $val;
			}
		}

		$re = $serv->insert_multi($insert_data);
		if ($re) {
			$re_su = json_encode(array('success', "操作成功"));
			echo $re_su;
			die;
		} else {
			$re_err = json_encode(array('error', '数据异常，操作失败'));
			die;
		}

	}

}
