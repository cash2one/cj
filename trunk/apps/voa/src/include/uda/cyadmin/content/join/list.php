<?php
class voa_uda_cyadmin_content_join_list extends voa_uda_cyadmin_content_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_cyadmin_content_join_list();
		}
	}

	/**
	 * 根据主键查询单条数据
	 * 
	 * @param $aid int        	
	 * @return $data array
	 *        
	 */
	public function get_view($jid) {
		return $this->__service->get($jid);
	}

	/**
	 * 添加数据
	 * 
	 * @param $data array()        	
	 * @return boolean
	 *
	 */
	public function add_job($data) {
		try {
			$this->__service->insert($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	/**
	 * 更新数据
	 * 
	 * @param $jid int        	
	 * @param $data array        	
	 *
	 */
	public function update_job($jid, $data) {
		try {
			$this->__service->update($jid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	public function del_job($ids) {
		try {
			$this->__service->delete($ids);
		} catch (Exception $e) {
			
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}
}
