<?php
/**
 * index.php
 * 移动派单/手机版入口文件
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_workorder_index extends voa_c_frontend_workorder_base {

	public function execute() {

		// 标记当前使用新版的手机模板目录
		// 此成员设置仅为新旧手机版本模板文件同时存在的一个过渡性判断
		// 未来旧版手机模板文件全部更换为新模板后可移除本设置
		// 此设置对应voa_c_frontend_base::_output()方法
		$this->_mobile_tpl = true;

		// 获取当前应用的唯一标识名
		list(,,,$plugin_identifier) = explode('_', rstrtolower(__CLASS__));

		// 将应用唯一标识名注入模板变量
		$this->view->set('plugin_identifier', $plugin_identifier);

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		$this->view->set('navtitle', $this->_plugin['cp_name']);

		// 引入应用模板
		$this->_output('mobile_v1/'.$plugin_identifier.'/index');
	}

}
