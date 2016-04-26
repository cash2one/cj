<?php
/**
 * voa_c_api_travel_post_goods
 * 更新商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goods extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取表格 dataid
		$dataid = 0;
		if (isset($this->_params['dataid'])) {
			$dataid = (int)$this->_params['dataid'];
		}

		try {
			$styles = (array)$this->_params['styles'];
			// 取默认产品规格中价格
			if (!empty($styles['price'])) {
				$price = 0;
				foreach ($styles['price'] as $_p) {
					if (1 > $price) {
						$price = round($_p, 2);
						continue;
					}

					$price = min($price, round($_p, 2));
				}

				$this->_params['price'] = $price;
			}

			if (1 > $this->_params['price']) {
				$this->_errcode = 511;
				$this->_errmsg = '价格不能小于 1 元';
				return true;
			}

			// 更新数据
			$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
			if (0 < $dataid) {
				if (!$uda->update($this->_member, $this->_params, $dataid)) {
					$this->_errcode = $uda->errno;
					$this->_errmsg = $uda->error;
					return true;
				}
			} else {
				$goods = array();
				if (!$uda->add($this->_member, $this->_params, $goods)) {
					$this->_errcode = $uda->errno;
					$this->_errmsg = $uda->error;
					return true;
				}

				$this->_result = $goods;
			}

			$dataid = empty($dataid) ? $goods['dataid'] : $dataid;
			// 更新产品规格
			$serv_st = new voa_s_oa_travel_styles();
			$st_multi = array();

			// 更新规格
			$is_none = true;
			foreach ($styles['stylename'] as $_id => $_name) {

				$price = round($styles['price'][$_id], 2);
				$amount = (int)$styles['amount'][$_id];
				if (1 > $price) {
					continue;
				}

				if (empty($_name)) {
					$_name = 'default';
				}

				$is_none = false;
				if (empty($styles['styleid'][$_id])) {
					$st_multi[] = array(
						'goodsid' => $dataid,
						'stylename' => $_name,
						'amount' => $amount,
						'price' => $price
					);
					continue;
				}

				$serv_st->update_by_conds(array('styleid' => $styles['styleid'][$_id], 'goodsid' => $dataid), array(
					'stylename' => $_name,
					'amount' => $styles['amount'][$_id],
					'price' => round($styles['price'][$_id], 2)
				));
			}

			// 如果规格数据为空
			if ($is_none && empty($st_multi)) {
				$st_multi[] = array(
					'goodsid' => $dataid,
					'stylename' => 'default',
					'amount' => -1,
					'price' => -1
				);
			}

			if (!empty($st_multi)) {
				$serv_st->insert_multi($st_multi);
			}

			// 删除不需要的数据
			$serv_st->delete_by_conds(array(
				'updated<?' => startup_env::get('timestamp'),
				'goodsid' => $dataid
			));

			// 指定用户
			$uda_mem = new voa_s_oa_travel_mem2goods();
			$uda_mem->delete_by_dataid($dataid);
			$uids = $this->request->get('uids');
			if (empty($uids)) {
				$uda_mem->insert(array(
					'uid' => 0,
					'username' => '',
					'dataid' => $dataid
				));
			} else {
				$serv_mem = new voa_s_oa_member();
				$users = $serv_mem->fetch_all_by_ids($uids);
				$inserts = array();
				foreach ($users as $_u) {
					$inserts[] = array(
						'uid' => $_u['m_uid'],
						'username' => $_u['m_username'],
						'dataid' => $dataid
					);
				}

				$uda_mem->insert_multi($inserts);
			}
		} catch (Exception $e) {
			$this->_errcode = 502;
			$this->_errmsg = '产品更新错误';
		}

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
