<?php
/**
 * voa_c_admincp_office_redpack_view
 * 红包-详情
 * Date: 15/3/9
 * Time: 上午10:42
 */


class voa_c_admincp_office_redpack_view extends voa_c_admincp_office_redpack_base {

	public function execute() {

		// 如果只是需要生成二维码
		$act = (string)$this->request->get('act', '');
		if ('qrcode' == $act) {
			return $this->_qrcode();
		}

		// 读取红包信息
		$uda_rp = &uda::factory('voa_uda_frontend_redpack_get');
		$redpack = array();
		$params = array('redpack_id' => $this->request->get('id', 0));
		if (!$uda_rp->doit($params, $redpack)) {
			return $this->_error_message($uda_rp->errmsg, '', '', false, $this->_self_url);
		}

		// 如果红包不存在
		if (empty($redpack)) {
			return $this->_error_message('当前红包不存在', '', '', false, $this->_self_url);
		}

		// 读取红包领取记录
		$uda_rplog = &uda::factory('voa_uda_frontend_redpack_getlogs');
		$rplist = array();
		$params = $this->request->getx();
		$params['redpack_id'] = $redpack['id'];
		if (!$uda_rplog->doit($params, $rplist)) {
			return $this->_error_message($uda_rplog->errmsg, '', '', false, $this->_self_url);
		}

		// 读取总数
		$serv_rp = &service::factory('voa_s_oa_redpack_log');
		$total = $serv_rp->count_by_redpack_id($redpack['id']);
		// 分页
		$multi = '';
		if ($total > 0) {
			$page = (int)$this->request->get('page', 1);
			list($start, $limit, $page) = voa_h_func::get_limit($page);
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
		}

		$this->view->set('multi', $multi);
		$this->view->set('redpack', $redpack);
		$this->view->set('rplist', $rplist);

		$this->output('office/redpack/view');
	}

	// 生成签到二维码
	protected function _qrcode() {

		// 跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . "{$_SERVER['HTTP_HOST']}/frontend/redpack/sign";
		voa_h_func::qrcode($url);
	}
}
