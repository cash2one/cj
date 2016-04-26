<?php
/**
 * talk.php
 * 产品咨询/手机版入口文件
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_talk extends voa_c_frontend_travel_base {

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 产品id
		$goodsid = $this->request->get('goodsid');

		// 读取数据
		$uda = new voa_uda_frontend_goods_data();
		$data = array();
		$uda->get_one($goodsid, $data);

		// 输出产品名称
		if ($data) {
			$this->view->set('subject', $data['subject']);
		}

		// 标记当前使用新版的手机模板目录
		// 此成员设置仅为新旧手机版本模板文件同时存在的一个过渡性判断
		// 未来旧版手机模板文件全部更换为新模板后可移除本设置
		// 此设置对应voa_c_frontend_base::_output()方法
		$this->_mobile_tpl = true;

		// 获取当前应用的唯一标识名
		list(,,,$plugin_identifier) = explode('_', rstrtolower(__CLASS__));

		// 将应用唯一标识名注入模板变量
		$this->view->set('plugin_identifier', $plugin_identifier);

		// 引入应用模板
		$this->_output('mobile_v1/'.$plugin_identifier.'/index');

		return true;
	}

}
