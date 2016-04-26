<?php
class voa_c_cyadmin_content_train_new extends voa_c_cyadmin_content_train_base {

	public function execute() {
		$data = array();
		$field = array();
		list($data, $field) = $this->_check_param($_POST);
		$ac = $this->request->post('ac');
		$uda = &uda::factory('voa_uda_cyadmin_content_train_list');
		switch ($ac) {
			case 'add' :
				if ($uda->add_train($data, $field)) {
					$this->message('success', '添加成功', $this->cpurl($this->_module, 'train', 'list', false));
				} else {
					$this->message('error', '添加失败，请检查');
				}
				break;
			case 'update' :
				$tid = $this->request->post('tid');
				if ($uda->update_train($tid, $data, $field)) {
					$this->message('success', '更新成功', $this->cpurl($this->_module, 'train', 'list', false));
				} else {
					$this->message('error', '更新失败，请检查');
				}
				break;
			default :
				$this->message('error', '非法操作！');
		}
	}

	protected function _check_param($request) {
		$data = array();
		$field = array();
		$data['title'] = strip_tags($request['title']);
		$data['source'] = !empty($request['source']) ? $request['source'] : '畅移云工作';
		$data['sourl'] = $request['sourl'];
		$data['description'] = $request['description'];
		$data['face_atid'] = !empty($request['face_atid']) ? $request['face_atid'] : 0;
		$data['is_publish'] = !empty($request['is_publish']) ? $request['is_publish'] : 1;
		$data['tsort'] = !empty($request['tsort']) ? $request['tsort'] : 0;
		$data['tags'] = $request['tags'];
		$data['content'] = $request['content'];
		$field['start_time'] = rstrtotime($request['start_time']['data'] . ' ' . $request['start_time']['time']);
		$field['end_time'] = rstrtotime($request['end_time']['data'] . ' ' . $request['end_time']['time']);
		$field['guests'] = $request['guests'];
		$field['address'] = $request['address'];
		$field['sign_fields'] = $request['sign_fields'];
		$this->_is_legal($data['title'], 2, 20, '线下培训标题在2-20个字符','utf-8');
		$this->_is_legal($data['description'], 2, 120, '摘要长度在2-120字符','utf-8');
		$this->_is_legal($data['content'], 2, 35000, '内容应在2-35000个字符','utf-8');
		$this->_is_legal($field['address'], 1, 10000, '地址应在1-10000个字符','utf-8');
		$this->_is_negative($data['tsort']);
		if (!empty($data['sourl'])) {
			$this->_is_url($data['sourl']);
		}
		
		return array(
			$data,
			$field 
		);
	}
}
