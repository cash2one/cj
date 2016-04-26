<?php
/**
 * index.php
 * 移动CRM/手机版入口文件
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_index extends voa_c_frontend_travel_basemp {

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	public function execute() {

		// 读取自定义主页
		$this->_get_diyindex();

		$this->view->set('classid', (int)$this->request->get('classid'));

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		//$this->view->set('navtitle', $this->_plugin['cp_name']);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/index');
	}

	// 获取自定义主页
	protected function _get_diyindex() {

		// 读取记录
		$uda_get = &uda::factory('voa_uda_frontend_travel_diyindex_get');
		$diyindex = array();
		if (!$uda_get->execute(array('uid' => (int)startup_env::get('saleuid')), $diyindex)) {
			return false;
		}

		// 如果为空, 则取默认首页
		if (empty($diyindex) && 0 < startup_env::get('saleuid')) {
			if (!$uda_get->execute(array('uid' => 0), $diyindex)) {
				return false;
			}
		}

		$indexes = array();
		if (!empty($diyindex) && is_array($diyindex['_message'])) {
			$indexes = $diyindex['_message'];
		}

		// 如果主页内容为空
		if (empty($indexes)) {
			return false;
		}

		$this->view->set('index', $diyindex);
		$this->view->set('indexs', $indexes);

		return true;
	}

}
