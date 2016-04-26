<?php
/**
 * 公司动态列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_category extends voa_c_frontend_news_base {

	public function execute() {

		$params = array(
			'nca_id' => rintval($this->request->get('nca_id')),
			'navtitle' => $this->request->get('navtitle'),
			'pluginid' => $this->request->get('pluginid')
		);
		$url = '/h5/index.html#/app/page/news/news-list?' . http_build_query($params);
		$this->redirect($url);
		return true;
		/** 公告类型ID */
		$nca_id = rintval($this->request->get('nca_id'));
		try{
			//读取公告类型
			$uda = &uda::factory ( 'voa_uda_frontend_news_category' );
			$category = $uda->get_category ( $nca_id );
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}
		//设置标题
		$this->view->set('navtitle', rhtmlspecialchars($category['name']));
		$this->view->set('nca_id', $nca_id);
		//如果有子类型，则属于后台菜单已更改而微信前台菜单还没更新的情况，定位到临时类型显示页；否则就显示正常的公共列表页
		if (isset($category['nodes'])) {
			$this->_output('mobile/news/category');
		} else {
			$this->_output('mobile/news/list');
		}

	}

}

