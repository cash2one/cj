<?php
/**
 * 城市数据词典
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_event_region extends voa_c_admincp_api_event_base {

	public function execute() {

		$type = $this->request->get('type');
		$id = $this->request->get('id');

		$resion = new voa_d_oa_region();

		$conds = array('parent_id' => $id);
		$region_data = $resion->list_by_conds($conds);

		$result = array(
			'list' => $region_data,
		);

		return $this->_output_result($result);
	}

}
