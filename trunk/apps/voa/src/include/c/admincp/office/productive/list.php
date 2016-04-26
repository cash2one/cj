<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_productive_list extends voa_c_admincp_office_productive_base {

	protected $_member_list = array();
	protected $_job_list = array();
	protected $_company_list = array();
	protected $_folder_list = array();

	public function execute() {

		$start_date = 0;
		$end_date = 0;
		$cache_config = voa_h_cache::get_instance()->get('plugin.productive.setting', 'oa');
		$this->view->set('cache_config', $cache_config);

		$act = $this->request->get('act');
		if ($act == 'getshoplist') {
			$name = $this->request->post('kw');
			echo json_encode($this->_get_shop_list($name));

			exit;
		} elseif ($act == 'view') {
			$pt_id = $this->request->get('pt_id');

			// 读取巡店详情表
			$db = &service::factory('voa_s_oa_productive');
			$productive = $db->fetch_by_id($pt_id);
			// 格工化巡店详情
			$this->_productive_format($productive);
			$productive_item = array();

			// 读取巡店所有打分项
			$db = &service::factory('voa_s_oa_productive_item');
			$productive_item_all = $db->fetch_all();

			// 读取父项
			foreach ($productive_item_all as $item) {
				if ($item['pti_parent_id'] == 0) {
					$productive_item[] = $item;
				}
			}
			// 读子项
			foreach ($productive_item as $key => $item) {
				foreach ($productive_item_all as $citem) {
					if ($citem['pti_parent_id'] == $item['pti_id']) {
						$productive_item[$key]['childs'][]  = $citem;
					}
				}
			}
			//$productive_score = $this->_service_single('productive_score', $this->_module_plugin_id, 'fetch_by_conditions', array('pt_id'=>$pt_id));
			// 读取打分记录
			$serv_isr = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
			$productive_score = $serv_isr->fetch_by_pt_id($pt_id);
			// 计算主评分项分数
			$total = 0;
			$item2score = array();
			$uda_base = &uda::factory('voa_uda_frontend_productive_base');
			$uda_base->calc_score($total, $item2score, $productive_score);
			if ($total < 0) {
				$total = 0;
			}
			$productive['score'] = $total;

			$db = &service::factory('voa_s_oa_productive_attachment');
			$productive_attachment_all = $db->fetch_by_conditions(array('pt_id'=>$pt_id));

			// 父项
			foreach ($productive_item as $key=>$item) {
				// 子项
				if (empty($productive_item[$key]['childs'])) {
					continue;
				}

				$item['total_score'] = $item2score[$item['pti_id']];
				foreach ($productive_item[$key]['childs'] as $ck=>$child) {
					// 子项分数
					foreach ($productive_score as $score) {
						if (isset($score['pti_id'])) {
							if ($score['pti_id'] == $child['pti_id']) {
								$pic = array();
								// 附件
								foreach ($productive_attachment_all as $att) {
									if ($att['ptsr_id'] == $score['ptsr_id']) {
										if ($att['at_id']) {
											//$productive_attachment = $this->_service_single('common_attachment', $this->_module_plugin_id, 'fetch_by_id', $att['at_id']);
											$productive_attachment = array();
											$productive_attachment['picurl'] = voa_h_attach::attachment_url($att['at_id'], 45);
											$productive_attachment['orgpicurl'] = voa_h_attach::attachment_url($att['at_id']);
											$pic[] = $productive_attachment;
										}
									}
								}
								$score['pic'] = $pic;

								//$item['total_score'] += $score['ptsr_score'];
								if (!empty($cache_config['score_rules'][$score['ptsr_score']])) {
									$score['diy'] = $cache_config['score_rules'][$score['ptsr_score']];
								}
								$child['score'] = $score;
							}
						}
					}
					$productive_item[$key]['total_score'] = $item['total_score'];
					$productive_item[$key]['childs'][$ck] = $child;
				}
			}

			$productive['productive_lists'] = $productive_item;

			$serv_mem = &service::factory('voa_s_oa_productive_mem', array('pluginid' => startup_env::get('pluginid')));
			$mem = $serv_mem->fetch_by_pt_id($pt_id);
			$data = array();
			foreach ($mem as $item) {
				if ($item['ptm_status'] == 3) {
					$data['bbc'] = $item['m_username'];
				} else {
					$data['receiver'] = $item['m_username'];
				}
			}
			$this->view->set('mem', $data);
			$this->view->set('productive', $productive);
		} else {

			$condi = array();
			$search = array();
			$search['city'] = array();
			$search['district'] = array();
			$post = array();
			if ($this->request->post('submit')) {
				$post = $this->request->postx();
			} elseif ($this->request->get('submit')) {
				$post = $this->request->getx();
			}
			if ($post) {
				$search = $post['search'];
				if ($search['assign_uid']) {
					$condi['m_uid'] = array($search['assign_uid'], 'in');

					$db = &service::factory('voa_s_oa_member');
					$assign_users = $db->fetch_all_by_ids(explode(',', $search['assign_uid']));
					$search['assign_users'] = array();
					foreach ($assign_users as $item) {
						$search['assign_users'][] = $item['m_username'];
					}
					if (!empty($search['assign_users'])) {
						$search['assign_users'] = implode(',', $search['assign_users']);
					}
				}

				if ($search['csp_ids']) {
					//$condi['csp_id'] = array($search['csp_ids'], 'in');

					$db = &service::factory('voa_s_oa_common_shop');
					$shops = $db->fetch_by_ids(explode(',', $search['csp_ids']));
					$search['csp_names'] = array();
					foreach ($shops as $item) {
						$search['csp_names'][] = $item['csp_name'];
						$condi['csp_id'][] = $item['csp_id'];
					}
					if (!empty($condi['csp_id'])) {
						$search['csp_names'] = implode(',', $search['csp_names']);
						$condi['csp_id'] = array($condi['csp_id'], 'in');

					}
				}
				if (!empty($search['district'])) {

					// for search form
					$db = &service::factory('voa_s_oa_common_region');
					$regions = $db->fetch_by_conditions(array('cr_parent_id'=>array($search['city'], 'in')));
					$search['district_org'] = $regions;
					//unset($regions);
					$db = &service::factory('voa_s_oa_common_shop');
					$shops = $db->fetch_by_conditions(array('cr_id'=>array($search['district'], 'in')));
					if ($shops) {
						$shop_ids = array();
						foreach ($shops as $val) {
							$shop_ids[] = $val['csp_id'];
						}
						$condi['csp_id'] = array($shop_ids, 'in');
					} else {
						$condi['csp_id'] = '';
					}


				} elseif (!empty($search['city'])) {

					$db = &service::factory('voa_s_oa_common_region');
					$regions = $db->fetch_by_conditions(array('cr_parent_id'=>array($search['city'], 'in')));
					// for search form
					$search['district_org'] = $regions;
					if ($regions) {

						$regions_ids = array();
						foreach ($regions as $val) {
							$regions_ids[] = $val['cr_id'];
						}
						$db = &service::factory('voa_s_oa_common_shop');
						$shops = $db->fetch_by_conditions(array('cr_id'=>array($regions_ids, 'in')));
						if ($shops) {
							$shop_ids = array();
							foreach ($shops as $val) {
								$shop_ids[] = $val['csp_id'];
							}
							$condi['csp_id'] = array($shop_ids, 'in');
						} else {
							$condi['csp_id'] = '';
						}
					} else {
						$condi['csp_id'] = '';
					}
				}

				if ($search['start_date']) {
					$start_date = rstrtotime($search['start_date'])-1;
				}

				if ($search['end_date']) {
					$end_date = rstrtotime($search['end_date'])+1;
				}

			}
			$this->view->set('search', $search);

			list($total, $multi, $list) = $this->_list($condi, $start_date, $end_date);
			$this->view->set('multi', $multi);
			$this->view->set('list', $list);
			$this->view->set('total', $total);
			$this->view->set('region', $this->_get_region_list());


		}

		$this->view->set('getRegionUrl', $this->cpurl($this->_module, $this->_operation, 'plan', $this->_module_plugin_id, array('act'=>'getregionlist')));
		$this->view->set('getShopUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getshoplist')));
		$this->view->set('getUsersUrl', $this->cpurl($this->_module, $this->_operation, 'plan', $this->_module_plugin_id, array('act'=>'getusers')));

		$this->view->set('viewUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'view', 'pt_id'=>'')));

		if ($act == 'view') {
			$this->output('office/productive/view_productive');
		} else {
			$this->output('office/productive/list');

		}
	}


	protected function _list($condi, $start_date, $end_date){
		$condi['pt_status'] = 3;
		// 每页显示数
		$perpage = 20;

		// 总数
		$db = &service::factory('voa_s_oa_productive');
		$total = $db->count_by_conditions($condi, $start_date, $end_date);

		// 分页显示
		$multi = '';
		// 管理员列表
		$list = array();

		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
		);

		$multi = pager::make_links($pager_options);

		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		// 管理员列表
		$list = $db->fetch_by_conditions($condi, $start_date, $end_date, $pager_options['start'], $pager_options['per_page']);

		// 格式化列表输出

		foreach ($list as &$_ca) {
			$this->_productive_format($_ca);
		}

		return array($total, $multi, $list);
	}

	protected function _productive_format(&$data) {

		if (!empty($data['csp_id'])) {
			$db = &service::factory('voa_s_oa_common_shop');
			$shop = $db->fetch_by_id($data['csp_id']);
			$data['csp_name'] = $shop['csp_name'];
			$db = &service::factory('voa_s_oa_common_region');
			$region = $db->fetch_by_id($shop['cr_id']);
			$data['district'] = $region['cr_name'];
			$region = $db->fetch_by_id($region['cr_parent_id']);
			$data['city'] = $region['cr_name'];
		}
		if (voa_d_oa_productive::STATUS_WAITING == $data['pt_status']) {
			$data['pt_status_text'] = "<label class='label label-info'>".voa_d_oa_productive::STATUS_WAITING_TEXT."</label>";
		} elseif (voa_d_oa_productive::STATUS_DONE == $data['pt_status']) {
			$data['pt_status_text'] = "<label class='label label-success'>".voa_d_oa_productive::STATUS_DONE_TEXT."</label>";
		} elseif (voa_d_oa_productive::STATUS_DOING == $data['pt_status']) {
			$data['pt_status_text'] = "<label class='label label-primary'>".voa_d_oa_productive::STATUS_DOING_TEXT."</label>";
		} elseif (voa_d_oa_productive::STATUS_REMOVE == $data['pt_status'] ) {
			$data['pt_status_text'] = "<label class='label label-danger'>".voa_d_oa_productive::STATUS_REMOVE_TEXT."</label>";
		}

		$data['pt_created'] = rgmdate($data['pt_updated'], 'Y-m-d H:i');
		$condi = array('pt_id'=>$data['pt_id'], 'pti_id'=>0);
		$db = &service::factory('voa_s_oa_productive_score');
		$score = $db->fetch_by_conditions($condi);
		$data['score'] = 0;
		if (!empty($score)) {
			$data['score'] = $score[array_rand($score)]['ptsr_score'];
		}
		$condi = array('pt_id'=>$data['pt_id']);
		$db = &service::factory('voa_s_oa_productive_mem');
		$mem_list = $db->fetch_by_conditions($condi);
		$data['receiver'] = '';
		$data['bbc'] = '';
		foreach ($mem_list as $item) {
			if ($item['ptm_status'] == 3) {
				$data['receiver'] = $item['m_username'];
			} elseif ($item['ptm_status'] == 4) {
				$data['bbc'] = $item['m_username'];
			}
		}


	}

	protected function _get_shop_list($name) {
		$newConditions = array();
		if (empty($name)) {
			return false;
		}
		$newConditions['csp_name'] = array("%$name%", 'like');

		$db = &service::factory('voa_s_oa_common_shop');
		$tmp	=	$db->fetch_by_conditions($newConditions);
		$ret = array();
		foreach ($tmp as $item) {
			$ret[] = $item;
		}

		return $ret;
	}

}
