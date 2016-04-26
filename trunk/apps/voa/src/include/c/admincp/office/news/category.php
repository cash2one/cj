<?php

/**
 * voa_c_admincp_office_news_category
 * 企业后台/微办公管理/新闻公告/类型设置
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_news_category extends voa_c_admincp_office_news_base {

	public function execute() {

		try {
			// 读取数据
			$uda = &uda::factory('voa_uda_frontend_news_category');
			$uda_wxqymenu = &uda::factory('voa_uda_frontend_application_wxqymenu_update');
			$new_categories = $uda->list_categories();

			$fixed = $this->_p_sets['fixed'];
			if ($this->_is_post()) {

				if(empty($_POST['fixed']['orderid'])){
					$_POST['fixed']['orderid'] = 1;
				}
				$new_cate = array();
				$uda->save_category($_POST, $new_cate);
				$is_publish = $_POST['is_publish'];
				if (!$is_publish) { //如果是保存
					//固定菜单
					$serv_set = &service::factory('voa_s_oa_news_setting');
					if (isset($_POST['new'])) {
						$fixed = $_POST['fixed'];
						//固定信息入库
						$fixed['checked'] = 1;
						$str_f = serialize($fixed);
						$serv_set->update_setting(array('fixed' => $str_f));
					} else {
						//固定信息入库
						$fixed = $_POST['fixed'];
						$def = array('name' => $fixed['name'], 'orderid' => $fixed['orderid'], 'checked' => 0);
						$str_f = serialize($def);
						$serv_set->update_setting(array('fixed' => $str_f));
					}
					//更新缓存操作
					$uda_base = &uda::factory('voa_uda_frontend_base');
					$uda_base->update_cache();
					$this->message('success', '保存成功', get_referer($this->cpurl($this->_module, $this->_operation, 'category', $this->_module_plugin_id)), false);
				} else { //如果是发布

					//组合菜单项
					$domain = config::get('voa.oa_http_scheme') . $this->_setting['domain'];
					$pluginid = $this->_p_sets['pluginid'];
					$new_categories = $uda->list_categories();

					if ($new_categories) {
						// 获取h5地址
						$menu = array();
						foreach ($new_categories as $k => $v) {
							if (isset($v['nodes'])) { //如果有子菜单
								$submenu = array();
								foreach ($v['nodes'] as $subv) {
									$submenu[] = array(
										'type' => 'view',
										'name' => $subv['name'],
										'url' => $domain . '/frontend/news/list?nca_id=' . $subv['nca_id'] . '&navtitle=' . $subv['name'] . '&pluginid=' . $pluginid,
									);
								}
								$menu[] = array(
									'name' => $v['name'],
									'sub_button' => $submenu,
								);
							} else { //如果没有子菜单
								$menu[] = array(
									'type' => 'view',
									'name' => $v['name'],
									'url' => $domain . '/frontend/news/list?nca_id=' . $v['nca_id'] . '&navtitle=' . $v['name'] . '&pluginid=' . $pluginid,
								);
							}
						}
					}
				}
				//固定菜单
				$serv_set = &service::factory('voa_s_oa_news_setting');
				if (isset($_POST['new'])) {
					$fixed = $_POST['fixed'];
					$tmp_f = array(
						'type' => 'view',
						'name' => $fixed['name'],
						'url' => $domain . '/frontend/news/template/?pluginid={pluginid}',
					);
					array_unshift($menu, $tmp_f);
					//固定信息入库
					$fixed['checked'] = 1;
					$str_f = serialize($fixed);
					$serv_set->update_setting(array('fixed' => $str_f));
				} else {
					//固定信息入库
					$fixed = $_POST['fixed'];
					$def = array('name' => $fixed['name'], 'orderid' => $fixed['orderid'], 'checked' => 0);
					$str_f = serialize($def);
					$serv_set->update_setting(array('fixed' => $str_f));
				}
				//更新缓存操作
				$uda_base = &uda::factory('voa_uda_frontend_base');
				$uda_base->update_cache();

				if ($uda_wxqymenu->doit(array('data' => $menu, 'pluginid' => $pluginid))) {
					$this->message('success', '发布成功', get_referer($this->cpurl($this->_module, $this->_operation, 'category', $this->_module_plugin_id)), false);
				}
			}
			$categories = $uda->list_categories();
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		$this->view->set('fixed', $fixed);
		$this->view->set('categories', $categories);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, 'category', $this->_module_plugin_id));
		// 输出模板
		$this->output('office/news/category');
	}

}
