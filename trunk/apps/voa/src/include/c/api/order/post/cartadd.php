<?php
/**
 * 购物车-添加产品
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_post_cartadd extends voa_c_api_order_abstract {

	// 创建订单接口
	public function execute() {

		$cartid = null;

		// 1.读产品,读规格
		try {
			// 读产品
			$cart = new voa_uda_frontend_travel_cart();
			$goods = array();
			$cart->get_goods($_POST['goods_id'], $goods);
			if (! $goods)
				return $this->_set_errcode('400:获取产品失败');

			$scale = $goods['percentage']; // 提成比例
			                               // 创建购物车产品数据
			$data = array(
				'openid' => $this->_member['openid'],
				'goods_id' => $goods['dataid'],
				'goods_name' => $goods['subject'],
				'num' => $_POST['num'] ? intval($_POST['num']) : 1,
				'price' => $goods['proto_2'] * 100,
				'scale' => $scale,
				'style_id' => $_POST['styleid']
			);

			// 如果传入规格id,则价格按规格表算
			if ($data['style_id']) {
				$style = array();
				// 判断规格是否存在, 数量是否超出
				if (! $cart->get_style($_POST['styleid'], $data['num'], $style)) {
					return $this->_set_errcode('401:' . $cart->errmsg);
				}

				$data['style_name'] = $style['stylename'];
				$data['price'] = $style['price'] * 100;
			} else {
				return $this->_set_errcode('403:请选择产品规格');
			}
			// $data['profit'] = $data['price'] * 20 / 100;

			// 读取售卖sales
			$this->_get_sales($data, $goods['dataid']);

			// 插入购物车产品表
			$rs = $cart->insert($data, $cartid);
			if (! $rs) {
				return $this->_set_errcode('402:插入购物车产品表失败');
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}
		$this->_result = $cartid;

		return true;
	}

	/**
	 * 读取销售信息
	 *
	 * @param unknown $data
	 * @param unknown $dataid
	 * @return boolean
	 */
	protected function _get_sales(&$data, $dataid) {

		// 获取能售卖该商品的用户
		$serv_tmg = &service::factory('voa_s_oa_travel_mem2goods');
		$sales = $serv_tmg->list_by_conds(array(
			'dataid' => $dataid
		));
		$cursale = array();
		// 遍历所有销售, 取出销售信息
		foreach ($sales as $_v) {
			// 如果销售信息为空, 则赋一个初始值
			if (empty($cursale)) {
				$cursale = $_v;
			}

			// 如果销售为当前销售, 则
			if ($_v['mg_uid'] == $this->session->get('saleuid')) {
				$cursale = $_v;
				break;
			}
		}

		// 不存在销售或未指定销售
		if (empty($cursale) || empty($cursale['mg_uid'])) {
			return true;
		}

		// 根据销售 uid, 读取用户
		$serv_m = &service::factory('voa_s_oa_member');
		if (! $mem = $serv_m->fetch_by_uid($cursale['mg_uid'])) {
			$this->session->remove('saleuid');
			return true;
		}

        if (!empty($mem['cd_id'])) {
			$data['cd_id'] = $mem['cd_id'];
        }
		$data['saleuid'] = $cursale['mg_uid'];

		if (!empty($cursale['mg_username'])) {
			$data['salename'] = $cursale['mg_username'];
		}

		// 更新客户和销售对应关系
		$serv_mp = &service::factory('voa_s_oa_mpuser');
		$serv_mp->update($this->_member['mpuid'], array('saleid' => $data['saleuid']));

		return true;
	}
}
