<?php
/**
 * voa_uda_frontend_sale_trajectory_insert
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_trajectory_insert extends voa_uda_frontend_base {

	//客户表
	protected $_trajectory;
	protected $_type;
	protected $_coustmer;
	
	public function __construct() {
		parent::__construct();
		$this->_trajectory = &service::factory('voa_s_oa_sale_trajectory');
		$this->_type = &service::factory('voa_s_oa_sale_type');
		$this->_coustmer = &service::factory('voa_s_oa_sale_coustmer');
	}
	
	/*
	 *插入或者修改
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {

		$val_trajectory = array (
			'scid' => array('_val_scid', 'int'),
			'stid' => array('_val_stid', 'int'),
			'at_ids' => array('_val_at_ids', 'string'),
			'longitude' => array('_val_longitude', 'string'),
			'latitude' => array('_val_latitude', 'string')
		);

		!empty($request['m_uid']) && $m_uid = rintval($request['m_uid']);
		if ($m_uid < 1) {
			$this->errmsg('301', '用户id不能为空');
			return false;
		}
		$this->_params = $request;

		$data = array();
		// 检查客户信息
		if (!$this->_submit2table($val_trajectory, $data)) {
			return false;
		}
		$data['content'] = isset($request['content']) ? $request['content'] : '';
		$data['present_address'] = isset($request['present_address']) ? $request['present_address'] : '';
		$data['m_uid'] = $request['m_uid'];

		try {
			$coustmer_data['type_stid'] = $data['stid'];
			$coustmer_data['type'] = $data['type'];
			$coustmer_data['color'] = $data['color'];

			$this->_coustmer->update_by_conds(array("scid" => $data['scid']), $coustmer_data);
			$result = $this->_trajectory->insert($data);

		} catch(Exception $e) {
			logger::error($e);
			$this->errmsg('110', '保存失败');
			return false;
		}
		$this->errmsg('0', '保存成功');
		return true;

		/*
		$data = array();
		$coustmer_data = array();
		//数据处理
		if(!empty($request['scid'])) {
			$data['scid'] = $request['scid'];
		}
		if(!empty($request['m_uid'])) {
			$data['m_uid'] = $request['m_uid'];
		}
		if(!empty($request['at_ids'])) {
			$data['at_ids'] = $request['at_ids'];
		}
		if(!empty($request['stid'])) {
			$data['stid'] = $request['stid'];
			$type = $this->type->get($request['stid']);
			$data['type'] = $type['name'];
			$data['color'] = $type['color'];
			
			$coustmer_data['type_stid'] = $request['stid'];
			$coustmer_data['type'] = $type['name'];
			$coustmer_data['color'] = $type['color'];
		}

		if(!empty($request['content'])) {
			$data['content'] = $request['content'];
		}
		if(!empty($request['present_address'])) {
			$data['present_address'] = $request['present_address'];
		}
		if(!empty($request['longitude'])) {
			$data['longitude'] = $request['longitude'];
		}
		if(!empty($request['latitude'])) {
			$data['latitude'] = $request['latitude'];
		}
		
		//插入
		$result = $this->trajectory->insert($data);
		if(!empty($coustmer_data)){
			$this->coustmer->update_by_conds(array("scid" => $request['scid']), $coustmer_data);
		}
		return true;
		*/
	}

	/**
	 * 检查客户
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_scid(&$scid, &$data) {

		$scid = trim($scid);

		if (empty($scid)) {
			$this->errmsg('302', '客户不存在');
			return false;

		}

		$coustmer = $this->_coustmer->get($scid);
		if (empty($coustmer)) {
			$this->errmsg('302', '客户不存在');
			return false;
		}

		$data['source'] = $coustmer['source_stid'];
		$data['scid'] = $scid;
		return true;
	}

	/**
	 * 检查轨迹状态
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_stid(&$stid, &$data) {

		$stid = trim($stid);

		if (empty($stid)) {
			$this->errmsg('303', '进度状态不存在');
			return false;

		}
		$type = $this->_type->get($stid);
		if (empty($type)) {
			$this->errmsg('303', '进度状态不存在');
			return false;
		}

		$data['stid'] = $stid;
		$data['type'] = $type['name'];
		$data['color'] = $type['color'];
		return true;
	}

	/**
	 * 判断附件是否有效
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_at_ids(&$at_ids, &$data) {

		$at_id_list = explode(',', $at_ids);
		$correct_at_id = array();

		if (!empty($at_id_list) &&
				is_array($at_id_list)) {

			foreach ($at_id_list as $at_id) {
				//判断附件是否存在
				$at = voa_h_attach::attachment_url($at_id, 0);
				if (!empty($at)) {
					$correct_at_id[] = $at_id;
				}
			}
		}

		$data['at_ids'] = implode(',', $correct_at_id);

		return true;
	}

	/**
	 * 检查经度
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_longitude(&$longitude, &$data) {

		$longitude = trim($longitude);

		if (!empty($longitude)) {
			if (is_numeric($longitude)) {
				$data['longitude'] = $longitude;
			} else{
				$this->errmsg('304', '坐标不正确');
				return false;
			}

		}
		return true;
	}

	/**
	 * 检查维度
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_latitude(&$latitude, &$data) {

		$latitude = trim($latitude);

		if (!empty($latitude)) {
			if (is_numeric($latitude)) {
				$data['latitude'] = $latitude;
			} else{
				$this->errmsg('304', '坐标不正确');
				return false;
			}

		}
		return true;
	}
}
