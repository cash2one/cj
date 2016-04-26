<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 16/1/18
 * Time: 11:28
 */

class voa_c_admincp_api_banner_order extends voa_c_admincp_api_banner_base {

	/**
	 * 标火
	 */
	public function ignite() {

		$bid = (int)$this->request->get('id');

		if (empty($bid) || !is_int($bid)) {
			return $this->_admincp_error_message(voa_errcode_oa_banner::NOT_NIT);
		}

		$serv = &service::factory('voa_s_oa_banner');
		$result = $serv->get($bid);
		if(empty($result)) {
			return $this->_admincp_error_message(voa_errcode_oa_banner::NOT_NIT);
		}

		$badge = ($result['badge'] == 0) ? 1 : 0;
		$data = array(
			'badge' => $badge
		);
		if(!$serv->update($bid, $data)){
			return $this->_admincp_error_message(voa_errcode_oa_banner::NOT_NIT);
		}
		//返回结果
		$result = array(
			'code' => 'ok',// 附件urls
		);

		return $this->_output_result($result);
	}

	/**
	 * 修改排序
	 */

	public function execute() {

		$order_list = $this->request->post('order');
		$order_id = $this->request->post('order_id');

		$data = array();
		foreach($order_list as $k => $v) {
			$data[] = array(
				'b_order' => $v,
				'bid' => $order_id[$k]
			);
		}
		$this->_update_order($data);

		$result = array(
			'code' => 'ok',// 附件urls
		);

		return $this->_output_result($result);
	}

	private function _update_order($in, &$out=array()) {

		$serv = &service::factory('voa_s_oa_banner');

		foreach ($in as $k => $val) {
			$serv->update($val['bid'], array('b_order' => $val['b_order']));
		}

		return true;
	}

}
