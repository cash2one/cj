<?php
/**
 * new.php
 * 新红包列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_new extends voa_c_frontend_redpack_base {

	public function execute() {

		// 判断用户权限
		if (empty($this->_p_sets['privilege_uids']) || !in_array($this->_user['m_uid'], $this->_p_sets['privilege_uids'])) {
			return $this->_error_message('您没有权限发红包');
		}

		/**
		 * select: 选择红包类型
		 * new: 新建红包
		 */
		$acs = array('select', 'new');
		$ac = (string)$this->request->get('ac', '');
		if (empty($ac) || !in_array($ac, $acs)) {
			$ac = 'select';
		}

		$this->view->set('navtitle', '发红包');
		$this->view->set('ac', $ac);
		$method = '_'.$ac;
		$this->$method();
	}

	/**
	 * 选择红包类型
	 */
	protected function _select() {

		$this->view->set('body_class', 'red-bg-red');
		$this->_output('mobile/redpack/add_select');
	}

	/**
	 * 新增红包
	 */
	protected function _new() {

		$type2tpl = array();
		$type2tpl[voa_d_oa_redpack::TYPE_AVERAGE] = 'average';
		$type2tpl[voa_d_oa_redpack::TYPE_RAND] = 'rand';
		$type2tpl[voa_d_oa_redpack::TYPE_APPOINT] = 'appoint';
		$type = (string)$this->request->get('type');
		if (empty($type) || !isset($type2tpl[$type])) {
			$type = voa_d_oa_redpack::TYPE_RAND;
		}

		// 统计用户数
		$serv_mem = &service::factory('voa_s_oa_member');
		$mem_ct = $serv_mem->count_all();

		$this->view->set('mem_ct', $mem_ct);
		$this->view->set('type', $type);
		$this->_output('mobile/redpack/add_new_'.$type2tpl[$type]);
	}

}
