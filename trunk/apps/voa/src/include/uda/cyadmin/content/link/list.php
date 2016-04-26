<?php
class voa_uda_cyadmin_content_link_list extends voa_uda_cyadmin_content_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_cyadmin_content_link_list();
		}
	}

	public function get_view($lid) {
		return $this->__service->get($lid);
	}

	public function add_link($data) {
		if ($this->__service->insert($data)) {
			return true;
		}
		
		return false;
	}

	public function update_link($lid, $data) {
		if ($this->__service->update($lid, $data)) {
			return true;
		}
		
		return false;
	}

	public function del_link($ids) {
		if ($this->__service->delete($ids)) {
			return true;
		}
		
		return false;
	}
}
