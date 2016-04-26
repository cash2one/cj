<?php
/**
 * list.php
 * 后台api/区域列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_api_region_list extends voa_c_admincp_api_region_base {

	public function execute() {

		try {
			// 载入区域列表uda
			$uda_region_list = new voa_uda_frontend_common_place_region_list();
			// 请求参数
			$request = array(
				'placetypeid' => (int)$this->request->get('placetypeid'),
				'placeregionid' => (int)$this->request->get('placeregionid')
			);
			// 其他配置参数
			$options = array();
			// 请求结果
			$result = array();
			// 请求uda
			$uda_region_list->doit($request, $result, $options);
		} catch (help_exception $h) {
			return $this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			return $this->_admincp_system_message($e);
		}

		// 格式化区域数据
		if (!empty($result['data'])) {
			uda::f('voa_s_oa_common_place_region')->format_list($result['data'], null, array(
				'placetypeid', 'remove', 'created', 'updated'
			));
		}

		return $this->_output_result($result);
	}

}
