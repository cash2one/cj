<?php
/**
 * voa_c_admincp_office_travel_order
 * 企业后台/微办公管理/营销CRM/销售管理
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_travel_sale extends voa_c_admincp_office_base {

	public function execute() {

		$this->view->set('pluginid', $this->_module_plugin_id);
		$act = $this->request->get('act');
		$acts = array(
			'auth'
		);
		$act = empty($act) || ! in_array($act, $acts) ? 'auth' : $act;
		// 加载子动作
		$func = '_' . $act;

		$this->$func();
	}

	protected function _auth() {

		$auth_code = (string)$this->request->get('auth_code');
		$expires_in = (int)$this->request->get('expires_in');
		$appid = $this->request->get('appid');
		$sigdata = array(
			'auth_code' => $auth_code,
			'expires_in' => $expires_in,
			'appid' => $appid,
			'sig' => (string)$this->request->get('sig'),
			'ts' => (int)$this->request->get('ts')
		);

		// 判断来源
		if (! empty($auth_code) && ! empty($expires_in)) {
			if (! voa_h_func::sig_check($sigdata)) {
				$this->_error_message('授权错误, 请重新进行授权');
				return true;
			}

			// 读取授权信息
			$serv_wo = voa_weixinopen_service::instance();
			$data = array();
			if (! $serv_wo->get_auth($data, $auth_code, $appid)) {
				$this->_error_message('授权信息读取失败, 请重新授权');
			}

			$serv_set = &service::factory('voa_s_oa_common_setting');
			// 更新服务号 appid
			$serv_set->update(array(
				'mp_appid' => $data['auth_appid']
			));

			$this->_success_message('授权成功');
			return true;
		}

		$parsed = parse_url(startup_env::get('boardurl'));
		$parts = array(
			'appid' => config::get('voa.weixin.crm_appid'),
			'domain' => $this->request->server('HTTP_HOST'),
			'path' => $parsed['path']
		);
		$this->view->set('auth_url', 'https://uc.vchangyi.com/uc/home/weopen?' . http_build_query($parts));

		$this->output('office/customize/sale');
	}

	protected function _def() {
		// 载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_sale');

		$searchDefault = array(
			'name' => '',
			'phone' => ''
		);

		$searchBy = array();
		$status = isset($_GET['sale_status']) ? intval($_GET['sale_status']) : 1;
		// 获取待申请的数量
		$rs = $uda->count($count);
		if ($count) {
			$this->view->set('count', $count);
		}

		$this->view->set('status', $status);
		$conditions = array(
			'sale_status' => $status
		);
		$this->_parse_search_cond($searchDefault, $searchBy, $conditions);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$limit = 12; // 每页显示数量
		$page = $this->request->get('page'); // 当前页码
		if (! is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 实际查询条件
		$list = array();
		$total = 0;
		if (! $uda->get_list($conditions, $page, $limit, $list, $total)) {
			$this->message('error', $uda_search->errmsg . '[Err:' . $uda_search->errcode . ']');
			return;
		}

		// 分页链接信息
		$multi = '';
		if ($total > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true
			));
		}

		// 注入模板变量
		$this->view->set('total', $total);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$prev = '/admincp/office/travel/sale/pluginid/' . $this->_module_plugin_id . '/';
		$this->view->set('prev', $prev);

		// 输出模板
		$this->output('office/customize/sale');
	}

	/**
	 * 重构搜索条件
	 *
	 * @param array $searchDefault
	 *        	初始条件
	 * @param array $searchBy
	 *        	输入的查询条件
	 * @param array $conditons
	 *        	组合的查询
	 */
	protected function _parse_search_cond($searchDefault, &$searchBy, &$conditons) {

		foreach ($searchDefault as $_k => $_v) {
			if (isset($_GET[$_k]) && $_v != $this->request->get($_k)) {
				$searchBy[$_k] = $this->request->get($_k);
				$conditons[$_k] = ($this->request->get($_k));
			}
		}
		return true;
	}

	/**
	 * 移除/拒绝(实际上就是删除)
	 */
	public function delete() {

		$id = $this->request->get('id');
		// 载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_sale');
		$sale = array();
		$rs = $uda->get($id, $sale);
		if (! $rs) {
			echo json_encode(array(
				'state' => 0,
				'msg' => '无此直销员'
			));
			return;
		}
		if ($sale['status'] == 3) {
			echo json_encode(array(
				'state' => 1,
				'msg' => '直销员已删除'
			));
			return;
		}
		$rs = $uda->delete($id);
		if ($rs) {
			echo json_encode(array(
				'state' => 1,
				'msg' => '移除成功'
			));
		} else {
			echo json_encode(array(
				'state' => 0,
				'msg' => '移除失败'
			));
		}
		exit();
	}

	/**
	 * 通过申请
	 */
	public function pass() {

		$id = $this->request->get('id');
		// 载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_sale');
		$sale = array();
		$rs = $uda->get($id, $sale);
		if (! $rs) {
			echo json_encode(array(
				'state' => 0,
				'msg' => '无此直销员'
			));
			return;
		}
		if ($sale['status'] == 3) {
			echo json_encode(array(
				'state' => 1,
				'msg' => '直销员已删除'
			));
			return;
		}
		$rs = $uda->pass($id);
		if ($rs) {
			echo json_encode(array(
				'state' => 1,
				'msg' => '操作成功'
			));
		} else {
			echo json_encode(array(
				'state' => 0,
				'msg' => '操作失败'
			));
		}
		exit();
	}

	public function get_qrcode() {
		// 获取映射id
		$id = $this->request->get('id');
		$qrcode = new voa_d_oa_travel_qrcode();
		$code = $qrcode->get($id);
		if (! $code) {
			echo json_encode(array('state' => 0, 'msg' => '获取映射id失败'));exit;
		}

		//获取微信二维码地址
		$wx_service = voa_weixin_service::instance();


		/** 获取二维码 ticket */
		$qrcode_url = '';
		if (!$wx_service->get_qrcode($qrcode_url, $code['code_id'])) {
			$this->_error_message('refresh_page');
		}
		if(!$qrcode_url) {
			echo json_encode(array('state' => 0, 'msg' => '获取二维码图片地址失败'));exit;
		}
		echo json_encode(array('state' => 1, 'msg' => $qrcode_url));
	}
}
