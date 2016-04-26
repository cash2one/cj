<?php
/**
 * edit.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_edit extends voa_c_admincp_system_shop_base {

	/** 当前编辑的门店ID */
	private $__placeid = 0;
	/** 当前编辑的门店信息 */
	private $__place = array();
	/** 所在区域信息 */
	private $__place_region = array();
	/** 相关人员 */
	private $__place_member = array();

	public function execute() {

		// 门店ID
		$this->__placeid = (int)$this->request->get('placeid');
		// 获取门店信息
		$uda_place_get = &uda::factory('voa_uda_frontend_common_place_get');
		// 获取的结果
		$result = array();
		try {
			$uda_place_get->doit(array('placeid' => $this->__placeid), $result, array('from_db' => 1));
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 格式化数据
		$this->__format_place_result($result);

		// 提交编辑请求
		if ($this->_is_post()) {
			$this->__response_submit();
		}

		// 门店信息
		$this->view->set('place', $this->__place);
		// 门店所在区域
		$this->view->set('place_region', $this->__place_region);
		// 门店负责人，供选人组件使用
		$this->view->set('default_master', rjson_encode($this->__place_member[voa_d_oa_common_place_member::LEVEL_CHARGE]));
		// 门店相关人，供选人组件使用
		$this->view->set('default_normal', rjson_encode($this->__place_member[voa_d_oa_common_place_member::LEVEL_NORMAL]));
		$this->view->set('form_submit_single_url'
				, $this->cpurl($this->_module, $this->_operation
						, $this->_subop, $this->_module_plugin_id
								, array('placeid' => $this->__placeid)));
		$this->view->set('placeid', $this->__placeid);

		$this->output('system/shop/edit');
	}

	/**
	 * 响应提交请求
	 * @return boolean
	 */
	private function __response_submit() {

		$uda = new voa_uda_frontend_common_place_edit();
		$uda_member = new voa_uda_frontend_common_place_member_update();

		// 更新门店信息的请求
		$request = array(
			'placeid' => $this->__placeid,
			'placeregionid' => (int)$this->request->post('placeregionid'),
			'name' => (string)$this->request->post('name'),
			'address' => (string)$this->request->post('address')
		);
		$result = array();
		$options = array();

		// 更新门店负责人的请求
		$request_master = array(
			'id' => $this->__placeid,
			'type' => voa_d_oa_common_place_member::TYPE_PLACE,
			'level' => voa_d_oa_common_place_member::LEVEL_CHARGE,
			'uid' => (array)$this->request->post('master_uid')
		);
		$result_master = array();
		$options_master = array();

		// 更新门店相关人的请求
		$request_normal = array(
			'id' => $this->__placeid,
			'type' => voa_d_oa_common_place_member::TYPE_PLACE,
			'level' => voa_d_oa_common_place_member::LEVEL_NORMAL,
			'uid' => (array)$this->request->post('normal_uid')
		);
		$result_normal = array();
		$options_normal = array();

		try {
			$uda->doit($request, $result, $options);
			$uda_member->doit($request_master, $result_master, $options_master);
			$uda_member->doit($request_normal, $result_normal, $options_normal);
		} catch (help_exception $h) {
			return $this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			return $this->_admincp_system_message($e);
		}

		$this->_admincp_success_message('编辑指定门店操作完毕'
				, $this->cpurl($this->_module, $this->_operation
						, $this->_subop, $this->_module_plugin_id
							, array('placeid' => $this->__placeid)), false);

		return true;
	}

	/**
	 * 格式化数据，以利于前端需要
	 * @param array $result
	 * @return void
	 */
	private function __format_place_result($result) {

		// 门店信息
		$this->__place = $result['place'];
		// 所在区域
		$this->__place_region = array();
		foreach ($result['region'] as $_k => $_v) {
			// 移除非必要的字段
			unset($_v['status'], $_v['deleted'], $_v['created'], $_v['updated']
					, $_v['remove'], $_v['placetypeid']);
			$this->__place_region[$_k] = $_v;
		}
		unset($_k, $_v);
		// 相关人员
		$this->__place_member = array();
		// 整理uid信息
		$uids = array();
		foreach ($result['user_list'] as $_list) {
			foreach ($_list as $_m) {
				$uids[] = $_m['uid'];
			}
			unset($_m);
		}
		unset($_list);
		// 获取人员信息
		$uda_getuserlist = new voa_uda_frontend_member_getuserlist();
		$user_list = array();
		try {
			$uda_getuserlist->doit(array('uids' => $uids), $user_list);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 整理以利于选人组件初始化使用
		$this->__place_member = array(
			voa_d_oa_common_place_member::LEVEL_CHARGE => array(),
			voa_d_oa_common_place_member::LEVEL_NORMAL => array()
		);
		if (!empty($result['user_list']) && is_array($result['user_list'])) {
			foreach ($result['user_list'] as $_level => $_list) {
				foreach ($_list as $_m) {
					$_user = voa_h_user::get($_m['uid']);
					if ($_level == voa_d_oa_common_place_member::LEVEL_CHARGE && $this->_p_set['place_master_count_max'] <= 1) {
						$this->__place_member[$_level][] = array(
							'id' => $_m['uid'],
							'name' => $_user['m_username'],
						);
					} else {
						$this->__place_member[$_level][] = array(
							'id' => $_m['uid'],
							'name' => $_user['m_username'],
						);
					}
					unset($_user);
				}
				unset($_m);
			}
			unset($_level, $_list);
		}
		//print_r($this->__place_member);

	}

}
