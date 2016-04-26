<?php
/**
 * voa_c_api_travel_post_goodsshare
 * 分享商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goodsshare extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取产品 goods_id
		$goods_id = (int)$this->_get('goods_id');
		// 读取数据
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$goods = array();
		if (!$uda->get_one($goods_id, $goods)) {
			$this->_set_errcode(voa_errcode_oa_goods::GOODS_DATA_IS_NOT_EXIST);
			return true;
		}

		// 判断是否有权限
		if (empty($goods) || $this->_member['m_uid'] != $goods['uid']) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_EDIT_PRIVILEGE);
			return true;
		}

		// 选项
		$opts = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
		// 获取分享 url 地址
		$url = '';
		$this->_get_share_url($url, $goods_id);
		// 分享返回数据
		$this->_result = array(
			'url' => $url,
			'recommend' => $opts[$goods['recommend']]['value'],
			'subject' => $goods['subject'],
			'cover' => empty($goods['cover']) ? '' : $goods['cover'][0]['url'],
			'price' => $goods['price'].$this->_get_col_unit('price')
		);

		return true;
	}

	/**
	 * 根据字段名获取单位
	 * @param string $col 字段名称
	 * @return boolean
	 */
	protected function _get_col_unit($col) {

		// 获取字段列表
		$cols = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$unit = '';
		foreach ($cols as $_v) {
			if ($col == $_v['field'] || $col == $_v['fieldalias'] || $col == '_'.$_v['tc_id']) {
				$unit = $_v['unit'];
				break;
			}
		}

		return $unit;
	}

	/**
	 * 获取分享链接
	 * @return boolean
	 */
	protected function _get_share_url(&$url, $goods_id) {

		// 协议
		$scheme = config::get(startup_env::get('app_name').'.oa_http_scheme');
		// 取 uid
		$uid = startup_env::get('wbs_uid');
		if (empty($uid) || 0 >= $uid) {
			$uid = (int)$this->_get('uid');
		}

		// 以产品 id 和 sales uid 来生成 sig
		$params = array(
			'goods_id' => $goods_id, // 产品 id
			'uid' => $uid, // 用户 uid
			'ts' => startup_env::get('timestamp') // 获取当前时间
		);

		// 生成秘钥
		$params['sig'] = voa_h_func::sig_create($params, $params['ts']);
		// 生成 url
		$url = $scheme.$this->_p_sets['domain'].'/frontend/goods/share?'.http_build_query($params);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

}
