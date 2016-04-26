<?php
/**
 * voa_uda_frontend_sale_coustmer_edit
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_coustmer_edit extends voa_uda_frontend_base {


	/**
	 * 客户表 service
	 * @var object
	 */
	protected $_coustmer;

	/**
	 * 自定义 service
	 * @var
	 */
	protected $_type;

	public function __construct() {
		parent::__construct();
		$this->_coustmer = &service::factory('voa_s_oa_sale_coustmer');
		$this->_type = &service::factory('voa_s_oa_sale_type');
	}
	
	/*
	 *插入或者修改
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {

		$val_coustmer = array (
			'companyshortname' => array('_val_companyshortname', 'string'),
			'company' => array('_val_company', 'string'),
			'address' => array('_val_address', 'string'),
			'name' => array('_val_name', 'string'),
			'phone' => array('_val_phone', 'string'),
			'source' => array('_val_source', 'int')
		);
		!empty($request['m_uid']) && $m_uid = rintval($request['m_uid']);
		if ($m_uid < 1) {
			$this->errmsg('102', '用户id不能为空');
			return false;
		}
		//if (!validator::is_string_count_in_range($request['companyshortname'], 1, 10)) {
		//	$this->errmsg('106', '简体不能小于1个字，大于10个字');
		//	return false;
		//}
		if($request['sfields'] != array(0=>'')) $val_coustmer['sfields'] = array('_val_fields', 'array');
		// 判断自定义字段必填项是否全填
		foreach ($request['sfields'] as $k => $v) {
			if (isset($v['required']) && $v['required'] == 1) {
				if ($v['value'] == '') {
					$this->errmsg('111', '还有必填项未填');
					return false;
				}
			}
		}
		$this->_params = $request;
		$scid = 0;
		$odata = array();

		//数据过滤

		if(!empty($request['scid'])) {
			$scid = $request['scid'];
			$scid = rintval($scid);
			//获取原始数据
			if (!empty($scid)) {
				$odata = $this->get_coustmer_by_id($scid);

			}
		}

		$data = array();
		// 检查客户信息
		if (!$this->_submit2table($val_coustmer, $data, $odata)) {
			return false;
		}
		try {
			if(!empty($scid)) {
				if (!empty($data)) {
					if (!empty($data['name'])) {
						if (!$this->_check_exist($data['name'], $request['phone'])) {
							return false;
						}
					}
					if (!empty($data['phone'])) {
						if (!$this->_check_exist($request['name'], $data['phone'])) {
							return false;
						}
					}

					$this->_coustmer->update_by_conds(array('scid' => $scid),$data);
				}
				$result['scid'] = $scid;
			} else {
				if (!$this->_check_exist($data['name'], $data['phone'])) {
					return false;
				}
				$data['type'] = "新增客户";
				$data['color'] = '#0080c0';
				$data['type_stid'] = 0;
				$data['m_uid'] = $request['m_uid'];
				$data['cm_uid'] = $request['m_uid'];
				$result = $this->_coustmer->insert($data);
			}
		} catch(Exception $e) {
			logger::error($e);
			$this->errmsg('110', '保存失败');
			return false;
		}
		$this->errmsg('0', '保存成功');
		return true;
	}

	/**
	 * 获取客户信息根据主键id
	 * @param $scid
	 * @return null
	 */
	public function get_coustmer_by_id($scid) {
		$scid = rintval($scid);
		if (empty($scid)) {
			return null;
		}
		return $this->_coustmer->get($scid);
	}

	/**
	 * 检查客户是否已存在
	 * @param $name
	 * @param $phone
	 * @return bool
	 */
	protected function _check_exist($name, $phone) {

		$coustmer = $this->_coustmer->count_by_conds(array('name =?' => $name, 'phone =?' => $phone));
		if ($coustmer > 0) {
			$this->errmsg('109', '联系人:'.$name.';联系方式:'.$phone.'的客户已存在');
			return false;
		}
		return true;
	}

	/**
	 * 检查公司简称
	 * @param $companyshortname
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_companyshortname(&$companyshortname, &$data, $odata) {

		$companyshortname = trim($companyshortname);

		if (empty($odata) ||
				$odata['companyshortname'] != $companyshortname) {

			if (!validator::is_string_count_in_range($companyshortname, 1, 10)) {
				$this->errmsg('101', '公司简称长度介于 1到10 个字符之间');
				return false;
			}

			$data['companyshortname'] = $companyshortname;
		}
		return true;
	}

	/**
	 * 检查公司全称
	 * @param $company
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_company(&$company, &$data, $odata) {

		$company = trim($company);

		if (empty($odata) ||
			$odata['company'] != $company) {

			$data['company'] = $company;
		}
		return true;
	}

	/**
	 * 检查公司地址
	 * @param $address
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_address(&$address, &$data, $odata) {

		$address = trim($address);

		if (empty($odata) ||
			$odata['address'] != $address) {

			if (!validator::is_addr($address)) {
				$this->errmsg('102', '请填写正确的公司地址');
				return false;
			}

			$data['address'] = $address;
		}
		return true;
	}

	/**
	 * 检查联系人
	 * @param $address
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_name(&$name, &$data, $odata) {

		$name = trim($name);

		if (empty($odata) ||
			$odata['name'] != $name) {

			if (!validator::is_string_count_in_range($name, 1, 50)) {
				$this->errmsg('103', '联系人长度介于 1到50 个字符之间');
				return false;
			}

			$data['name'] = $name;
		}
		return true;
	}

	/**
	 * 检查联系人
	 * @param $address
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_phone(&$phone, &$data, $odata) {

		$phone = trim($phone);

		if (empty($odata) ||
			$odata['phone'] != $phone) {

			if (!validator::is_mobile($phone) &&
					!validator::is_phone($phone)) {
				$this->errmsg('104', '联系方式请填写手机号或带区号的联系电话');
				return false;
			}

			$data['phone'] = $phone;
		}
		return true;
	}

	/**
	 * 检查客户来源
	 * @param $address
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_source(&$source, &$data, $odata) {

		$source_stid = rintval($source);

		if (empty($odata) ||
			$odata['source_stid'] != $source_stid) {

			$source_data = $this->_type->get($source_stid);
			if (empty($source_data)) {
				$this->errmsg('105', '客户来源不存在');
				return false;
			}

			$data['source_stid'] = $source_stid;
			$data['source'] = $source_data['name'];
		}
		return true;
	}


	/**
	 * 检查客户来源
	 * @param $address
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_fields(&$fields, &$data, $odata) {
		if (empty($fields) ||
				!is_array($fields)) {
			return true;
		}

		$field_keys = array_column($fields, 'key');
		$type_fields = $this->_type->list_by_conds(array('stid IN (?)' => $field_keys, 'type =?' => voa_d_oa_sale_type::TYPE_FIELD));
		foreach ($fields as &$field) {
			if (!empty($type_fields[$field['key']])) {
				$field['name'] = $type_fields[$field['key']]['name'];
			}
		}

		$serial_fields = json_encode($fields);
		if (empty($odata) ||
				$odata['sfields'] != $serial_fields) {
			$data['sfields'] = $serial_fields;
		}

		return true;
	}
}
