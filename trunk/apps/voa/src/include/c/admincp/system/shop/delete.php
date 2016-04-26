<?php
/**
 * shop.php
 * 云工作后台/系统设置/门店管理/门店删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_delete extends voa_c_admincp_system_shop_base {

	public function execute() {

		// 请求删除的门店ID(可能是单个也可能是多个)
		$placeid = $this->request->get('placeid');

		// 待删除的id列表
		$ids = array();
		if (is_array($placeid)) {
			$ids = $placeid;
		} else {
			$ids = array($placeid);
		}

		// 整理待删除的门店id数组
		$ids = rintval($ids, true);
		// 移除重复的
		$ids = array_unique($ids);
		if (empty($ids) || empty($placeid)) {
			return $this->_admincp_error_message(101, '请选择待删除的门店');
		}

		// 载入uda
		$uda_place_delete = &uda::factory('voa_uda_frontend_common_place_delete');
		// 请求
		$request = array(
			'placeid' => $ids
		);
		// 结果
		$result = array();
		try {
			voa_uda_frontend_transaction_abstract::s_begin();
			$uda_place_delete->doit($request, $result);
			voa_uda_frontend_transaction_abstract::s_commit();
		} catch (help_exception $h) {
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			voa_uda_frontend_transaction_abstract::s_rollback();
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		$this->_admincp_success_message('指定门店删除完毕', $this->cpurl($this->_module, $this->_operation
				, 'list', $this->_module_plugin_id));
	}

}
