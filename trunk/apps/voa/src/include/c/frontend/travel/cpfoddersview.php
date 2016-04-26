<?php
/**
 * cpfoddersview.php
 * 商品素材详情
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpfoddersview extends voa_c_frontend_travel_base {

	public function execute() {

		//商品id
		$goodsid = $this->request->get('goodsid');

		// 读取数据
		$data = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data',$this->_ptname);
		if (!$uda->get_one($goodsid, $data)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		//素材短链为空，调用微信接口生成短链,并且入库
		if (empty($data['fodder_link'])) {
			$scheme = config::get('voa.oa_http_scheme');//获取域名
			$sets = voa_h_cache::get_instance()->get('setting','oa');
			$url = $scheme.$sets['domain'].'/frontend/travel/viewgoods?goodsid='.$goodsid;
            //调用微信接口生成短链
			$serv = new voa_weixin_service();
			$shorturl = $serv->get_short_url($url);

			$gp['fodder_link']=$shorturl;
			$uda->update_fodder($goodsid,$gp);
			$data['fodder_link']= $shorturl;
		}

		//读取素材图片
		if (!empty($data['fodder_img'])) {
			$this->view->set('attachs', $data['fodder_img']); //素材图片
		}


		$this->view->set('fodders',$data);


		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpfoddersview');
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}
}
