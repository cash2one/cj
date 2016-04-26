<?php
class voa_c_cyadmin_content_link_new extends voa_c_cyadmin_content_link_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_link_list');
		$data = array();
		$this->_check_param($_POST, $data);
		if ($_POST['ac'] == 'add') { // 代表进行新增
			if ($uda->add_link($data)) {
				$this->message('success', '新增成功', $this->cpurl($this->_module, 'link', 'list'), false);
			} else {
				$this->message('error', '新增失败！');
			}
		} elseif ($_POST['ac'] == 'update') { // 代表更新
			$jid = $_POST['lid'];
			if ($uda->update_link($jid, $data)) {
				$this->message('success', '更新成功', $this->cpurl($this->_module, 'link', 'list'), false);
			} else {
				$this->message('error', '更新失败！');
			}
		} else {
			$this->message('error', '非法操作！');
		}
	}

	protected function _check_param($rquest, &$data) {
		$data['linkname'] = strip_tags($rquest['linkname']);
		$data['linkurl'] = $rquest['linkurl'];
		$data['lsort'] = $rquest['lsort'];
		$data['linktype'] = $rquest['linktype'];
		$data['is_publish'] = !empty($rquest['is_publish']) ? $rquest['is_publish'] : 1;
		$this->_is_legal($data['linkname'], 2, 100, '链接名称应在2-100个字符','utf-8');
		
		$this->_is_url($data['linkurl']);
		$this->_is_negative($data['lsort']);
		if ($data['linktype'] == 1) {
			$data['companyname'] = $rquest['companyname'];
			$this->_is_legal($data['companyname'], 2, 100, '企业名称应在2-100个字符','utf-8');
		} elseif ($data['linktype'] == 2) {
			$data['atid'] = !empty($rquest['atid']) ? $rquest['atid'] : 0;
			if ($data['atid'] == 0) {
				$this->message('error', '请上传企业LOGO');
			}
		} else {
			$this->message('error', '请选择正确的链接类型！');
		}
		
		return true;
	}
}
