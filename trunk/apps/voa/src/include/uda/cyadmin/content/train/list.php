<?php
class voa_uda_cyadmin_content_train_list extends voa_uda_cyadmin_content_base {
	private $__service = null;
	private $__service_field = null;
	private $__service_sign = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_cyadmin_content_train_list();
		}
		
		if ($this->__service_field == null) {
			$this->__service_field = new voa_s_cyadmin_content_train_field();
		}
		
		if ($this->__service_sign == null) {
			$this->__service_sign = new voa_s_cyadmin_content_train_sign();
		}
	}

	/**
	 * 添加培训
	 * 
	 * @param $data array        	
	 * @param $field array        	
	 *
	 */
	public function add_train($data, $field) {
		try {
			$this->__service->begin();
			$data = $this->__service->insert($data);
			$field['tid'] = $data['tid'];
			$this->__service_field->insert($field);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	/**
	 * 修改培训
	 */
	public function update_train($id, $data, $field) {
		try {
			$this->__service->begin();
			$this->__service->update($id, $data);
			$conds = array();
			$conds["tid=?"] = $id;
			$this->__service_field->update_by_conds($conds, $field);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	/**
	 * 删除培训
	 */
	public function del_train($ids) {
		try {
			$this->__service->begin();
			$this->__service->delete($ids);
			$conds = array(
				'tid' => $ids 
			);
			$this->__service_field->delete_by_conds($conds);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	/**
	 * 获取培训详情
	 */
	public function get_view($id) {
		$view = array();
		$data = array();
		$fields = array();
		$conds = array();
		$_fields = array();
		$data_main = $this->__service->get($id);
		$conds["tid=?"] = $id;
		$data_field = $this->__service_field->get_by_conds($conds);
		$data = array_merge($data_main, $data_field);
		$view = $data;
		$_signfield = explode(',', $data['sign_fields']);
		$serv_sign = &service::factory('voa_s_cyadmin_content_train_setting');
		$fields = $serv_sign->list_all();
		foreach ($fields as $val) {
			$_fields[$val['sid']] = $val['fieldname'];
		}
		$sign_fields = array();
		foreach ($_signfield as $v) {
			if (array_key_exists($v, $_fields)) {
				$sign_fields[] = $_fields[$v];
			}
		}
		$view['sign_fields_info'] = $sign_fields;
		
		return $view;
	}
}
