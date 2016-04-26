<?php

/**
 * voa_c_cyadmin_manage_base
 * 主站后台/后台管理/基本控制器
 * Create By Mojianyuan
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_base extends voa_c_cyadmin_base {

	/** 管理组表 service */
	protected $_serv_profile = null;
	protected $_serv_app = null;
	protected $_serv_sms = null;
	protected $_serv_acc = null;
	protected $_uda_profile = null;
	protected $_serv_paysetting = null;
	protected $_serv_salessetting = null;
	protected $_serv_message = null;
	protected $_serv_message_log = null;
	protected $_serv_apppay = null;
	protected $_serv_trial = null;
	protected $_serv_pay_standard = null;
	protected $_serv_pay_special = null;
	protected $_serv_operationrecord = null;

	// 所有代理商信息
	protected $_all_agent = array();
	// 规模
	protected $_scale = array();
	// 行业
	protected $_industry = array();
	// 客户状态
	protected $_customer_status = array();
	// 客户等级
	protected $_customer_level = array();
	// 付费状态
	protected $_pay_status = array();

	/** 所有的应用ID */
	protected $_domain_plugin = array();

	/** 分组应用列表 */
	protected $_domain_plugin_list = array();

	/** 所有管理员信息 */
	protected $_adminer_data = array();

	//有和没有
	const EP_TRUE = 1;
	const EP_FALSE = 2;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_serv_profile = &service::factory('voa_s_cyadmin_enterprise_profile', array('pluginid' => 0));
		$this->_serv_acc = &service::factory('voa_s_cyadmin_enterprise_account', array('pluginid' => 0));
		$this->_serv_app = &service::factory('voa_s_cyadmin_enterprise_app', array('pluginid' => 0));
		$this->_serv_sms = &service::factory('voa_s_uc_sms', array('pluginid' => 0));
		$this->_serv_paysetting = &service::factory('voa_s_cyadmin_company_paysetting');
		$this->_serv_salessetting = &service::factory('voa_s_cyadmin_company_salessetting');
		$this->_serv_message = &service::factory('voa_s_cyadmin_enterprise_message');
		$this->_serv_message_log = &service::factory('voa_s_cyadmin_enterprise_message_log');
		$this->_serv_trial = &service::factory('voa_s_cyadmin_company_trial');
		$this->_serv_pay_standard = &service::factory('voa_s_cyadmin_company_paysetting_standard');
		$this->_serv_pay_special = &service::factory('voa_s_cyadmin_company_paysetting_special');
		$this->_serv_operationrecord = &service::factory('voa_s_cyadmin_company_operationrecord');

		$this->_serv_namecard = &service::factory('voa_s_cyadmin_recognition_namecard', array('pluginid' => 0));
		$this->_serv_namecard_backup = &service::factory('voa_s_cyadmin_recognition_namecard_backup', array('pluginid' => 0));
		$this->_serv_bill = &service::factory('voa_s_cyadmin_recognition_bill', array('pluginid' => 0));
		$this->_serv_bill_backup = &service::factory('voa_s_cyadmin_recognition_bill_backup', array('pluginid' => 0));

		$this->_uda_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');

		// 获取所有代理商信息
		$this->_all_agent = $this->_serv_acc->list_all();
		// 规模
		$this->_scale = config::get('voa.enterprise.companysize');
		// 行业
		$this->_industry = array(
			"互联网/电子商务",
			"房地产/建筑/建材/工程",
			"旅游/度假",
			"酒店/餐饮",
			"电子技术/半导体/集成电路",
			"计算机硬件",
			"IT服务(系统/数据/维护)",
			"通信/电信/网络设备",
			"通信/电信运营、增值服务",
			"网络游戏",
			"基金/证券/期货/投资",
			"保险",
			"银行",
			"信托/担保/拍卖/典当",
			"家居/室内设计/装饰装潢",
			"物业管理/商业中心",
			"专业服务/咨询(财会/法律/人力资源等)",
			"广告/会展/公关",
			"中介服务",
			"检验/检测/认证",
			"外包服务",
			"快速消费品（食品/饮料/烟酒/日化）",
			"耐用消费品（服饰/纺织/皮革/家具/家电）",
			"贸易/进出口",
			"零售/批发",
			"租赁服务",
			"教育/培训/院校",
			"礼品/玩具/工艺美术/收藏品/奢侈品",
			"汽车/摩托车",
			"大型设备/机电设备/重工业",
			"加工制造（原料加工/模具）",
			"仪器仪表及工业自动化",
			"印刷/包装/造纸",
			"办公用品及设备",
			"医药/生物工程",
			"医疗设备/器械",
			"航空/航天研究与制造",
			"交通/运输",
			"物流/仓储",
			"医疗/护理/美容/保健/卫生服务",
			"媒体/出版/影视/文化传播",
			"娱乐/体育/休闲",
			"能源/矿产/采掘/冶炼",
			"石油/石化/化工",
			"环保",
			"政府/公共事业/非盈利机构",
			"学术/科研",
			"农/林/牧/渔",
			"跨领域经营",
			"其他"
		);
		// 客户状态
		$this->_customer_status = array(
			1 => '新增客户',
			2 => '初步沟通',
			3 => '见面拜访',
			4 => '确定意向',
			5 => '正式报价',
			6 => '商务谈判',
			7 => '签约成交',
			8 => '售后服务',
			9 => '停滞',
			10 => '流失'
		);
		// 客户等级
		$this->_customer_level = array(
			1 => '小客户',
			2 => '中型客户',
			3 => '大型客户',
			4 => 'VIP客户'
		);
		// 付费状态
		$this->_pay_status = array(
			1 => '已付费',
			2 => '已付费-即将到期',
			3 => '已付费-已到期',
			5 => '试用期-即将到期',
			6 => '试用期-已到期',
			7 => '试用期'
		);

		// 获取所有 人员 应用 应用列表 信息
		if (substr($_SERVER['HTTP_HOST'], -3) != 'net') { // 本地调试的时候 没有RPC 就不会报错
			$this->_domain_plugin_list = voa_h_cache::get_instance()->get('domain_applist', 'cyadmin');
		}
		$this->_adminer_data = voa_h_cache::get_instance()->get('adminer', 'cyadmin');

		return true;
	}

	/**
	 * 获取消息列表
	 * @param $data
	 * @return bool
	 */
	protected function _message_list(&$data) {

		// 获取列表
		$news = &uda::factory('voa_uda_cyadmin_enterprise_news');
		$page1 = $this->request->get('page');
		$multi1 = '';
		$msg_list1 = array();
		$total1 = '';
		$news->getlist($page1, $msg_list1, $multi1, $total1);
		$data1 = array();
		$news->format($msg_list1, $data1);
		// 返回数据
		$data = array($data1, $multi1, $total1);

		return true;
	}

	/**
	 * 把二维数组array1里的一个ID和array2里的ID所属信息合并
	 * @param array $array0 二维数组0
	 * @param array $array1 关联数组1
	 * @param string $keyword0 数组0里的关键字
	 * @param string $keyword1 关联的关键字
	 * @return bool
	 */
	protected function _merge_relation_array(&$array0, $array1, $keyword0, $keyword1) {
		if (!empty($array0)) {
			foreach ($array0 as $k => &$v) {
				if (!empty($array1)) {
					foreach ($array1 as $k1 => $v1) {

						if (isset($v1[$keyword1])) {
							if ($v[$keyword0] == $v1[$keyword1]) {
								$v = array_merge($v, $v1);
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * 格式化企业信息
	 * @param array $adminergroup
	 * @return array
	 */
	protected function _profile_format($profile) {

		if (isset($profile['ep_updated']) && !empty($profile['ep_updated'])) {
			$profile['ep_updated'] = rgmdate($profile['ep_updated'], 'Y-m-d H:i');
			$profile['_updated'] = $profile['ep_updated'];
		}
		if (isset($profile['ep_created']) && !empty($profile['ep_created'])) {
			$profile['ep_created'] = rgmdate($profile['ep_created'], 'Y-m-d H:i');
			$profile['_created'] = $profile['ep_created'];
		}
		$profile['ep_locked_text'] = (!empty($profile['ep_locked']) ? '锁定' : '正常');

		return $profile;
	}


	/**
	 * 企业列表
	 * @return array
	 */
	protected function _enterpriseapp_list($id) {
		// 每页显示数
		$perpage = 20;

		// 管理员总数
		$total = $this->_serv_app->count_all_by_id($id);
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

		$list = $this->_serv_app->fetch_all_by_id($id, $pager_options['start'], $pager_options['per_page']);


		// 格式化列表输出
		foreach ($list as &$_ca) {
			$_ca = $this->_app_format($_ca);
		}
		unset($_ca);

		return array($total, $multi, $list);


	}

	protected function _profile_condi_format(&$arr) {
		return $arr;
	}

	/**
	 * 企业列表
	 * @return array
	 */
	protected function _profile_list($condi, $date_start = 0, $date_end = 0) {
		$this->_profile_condi_format($condi);
		// 每页显示数
		$perpage = 20;

		// 管理员总数
		$total = $this->_serv_profile->count_by_conditions($condi, $date_start, $date_end);
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


		/**
		 * 根据条件计算总数
		 * @param  array $conditions
		 *  $conditions = array(
		 *      'field1' => '查询条件', // 运算符为 =
		 *      'field2' => array('查询条件', '查询运算符'),
		 *      'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
		 *      ...
		 *  );
		 * @return number
		 */

		// 管理员列表
		$list = $this->_serv_profile->fetch_by_conditions($condi, $pager_options['start'], $pager_options['per_page'], $date_start, $date_end);

		// 格式化列表输出
		if (is_array($list)) {
			foreach ($list as &$_ca) {
				$_ca = $this->_profile_format($_ca);
				$conds = array(
					'id = ?' => $_ca['ep_id']
				);
				$get = $this->_serv_salessetting->list_by_conds($conds, array(
					0,
					1
				), array('updated' => 'DESC'));
				if (!empty($get[0])) {
					$_ca['sales'] = $get[0];
				}
			}
		}
		unset($_ca);

		return array($total, $multi, $list);
	}

	protected function _app_get($ea_id) {
		$item = $this->_serv_app->fetch($ea_id);

		return $this->_app_format($item);
	}

	protected function _save_app($value, $ea_id, $ep_id = 0) {
		if (!empty($value['ea_appstatus'])) {

			$item = $this->_app_get($ea_id);

			// 设置开通、关闭、删除应用操作
			$uda_application_app = &uda::factory('voa_uda_cyadmin_enterprise_app');

			// 开通
			if ($item['ea_appstatus'] == voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_OPEN) {
				$type = 'open';
				$value['ea_appstatus'] = voa_d_cyadmin_enterprise_app::APPSTATUS_OPEN;
			} elseif ($item['ea_appstatus'] == voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_DELETE) {
				$type = 'delete';
				$value['ea_appstatus'] = voa_d_cyadmin_enterprise_app::APPSTATUS_DELETE;
			} elseif ($item['ea_appstatus'] == voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_CLOSE) {
				$type = 'close';
				$value['ea_appstatus'] = voa_d_cyadmin_enterprise_app::APPSTATUS_CLOSE;
			}
			$value['ea_updated'] = startup_env::get('timestamp');
			$value['ea_status'] = 2;
			$profile = $this->_serv_profile->fetch($ep_id);
			$domain = $profile['ep_domain'];

			if ($uda_application_app->post_to_oasite($domain, $type, $ea_id)) {
				echo "操作成功";
				// 操作成功
			} else {
				if (!$uda_application_app->errno == '409') {
					// 操作失败
					echo $uda_application_app->error, ':num=', $uda_application_app->errno;
					exit;
				} else {
					echo "操作成功";
				}

			}

		}
		$this->_serv_app->update($value, $ea_id);
	}

	protected function _save_profile($value, $id) {
		if (!empty($value['ep_statuswx'])) {
			if ($value['ep_statuswx'] == '1') {
				// 更新wxcorp 到oa站
				$uda_application_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');
				$profile = $this->_profile_get($id);
				//if ($profile['ep_domain'] && $profile['ep_wxcorpid'] && $profile['ep_wxcorpsecret']) {
				$corp = array();
				$corp['ep_wxcorpid'] = $profile['ep_wxcorpid'];
				$corp['ep_wxcorpsecret'] = $profile['ep_wxcorpsecret'];
				$corp['ep_wxtoken'] = $profile['ep_wxtoken'];
				$corp['ep_xgaccessid'] = $profile['ep_xgaccessid'];
				$corp['ep_xgaccesskey'] = $profile['ep_xgaccesskey'];
				$corp['ep_xgsecretkey'] = $profile['ep_xgsecretkey'];
				$corp['ep_qrcode'] = $profile['ep_qrcode'];
				$corp['ep_wxname'] = $profile['ep_wxname'];
				if (!$uda_application_profile->post_corp_to_oa($profile['ep_domain'], $corp)) {
					echo '更新微信 corp ID & corpSECRET到OA站失败.原因：', $uda_application_profile->error, '=', $uda_application_profile->errno;
				}
				//}
			}
		}
		if (isset($value['ep_locked'])) {
			// 更新locked状态到oa站
			$uda_application_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');
			$profile = $this->_profile_get($id);
			if (!$uda_application_profile->post_corp_to_oa($profile['ep_domain'], array('ep_locked' => $value['ep_locked']))) {
				echo '更新锁定 locked到OA站失败.原因：', $uda_application_profile->error, '=', $uda_application_profile->errno;
			}
		}


		$this->_serv_profile->update($value, array('ep_id' => $id));
		$serv_uc = &service::factory('voa_s_uc_enterprise');
		foreach ($value as $key => $val) {
			if (in_array($key, array('ep_adminrealname'))) {
				$serv_uc->update($value, $id);
			}
			if ($key == 'ep_adminmobile') {
				$da['ep_adminmobilephone'] = $value['ep_adminmobile'];
				$serv_uc->update($da, $id);
			}
		}

	}

	protected function _nobeginning_total() {
		$condi = array('ep_statusep = 0', 'ep_statuswx=0', 'ep_statusmail=0', 'ep_statusall=0');
		$total = $this->_serv_profile->count_by_conditionsstr('(' . implode(' OR ', $condi) . ')', array());

		return $total;
	}

	protected function _nostatusep_total() {
		$condi = array('ep_statusep' => 0);
		$total = $this->_serv_profile->count_by_conditions($condi);

		return $total;
	}

	protected function _nostatuswx_total() {
		$condi = array('ep_statuswx' => 0);
		$total = $this->_serv_profile->count_by_conditions($condi);

		return $total;
	}

	protected function _nostatusmail_total() {
		$condi = array('ep_statusmail' => 0);
		$total = $this->_serv_profile->count_by_conditions($condi);

		return $total;
	}

	protected function _statusall_total() {
		$condi = array('ep_statusall' => 1);
		$total = $this->_serv_profile->count_by_conditions($condi);

		return $total;
	}

	protected function _get_reccard_end_id() {
		$condi['ca_id'] = '';
		$lists = $this->_serv_namecard->fetch_by_conditions($condi, 0, 1, $order = 'DESC');
		$id = 0;
		if (!empty($lists)) {
			$id = $lists[0]['rnc_id'];
		}

		return $id;
	}

	/**
	 * 根据条件计算总数
	 * @param  array $conditions
	 *  $conditions = array(
	 *      'field1' => '查询条件', // 运算符为 =
	 *      'field2' => array('查询条件', '查询运算符'),
	 *      'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *      ...
	 *  );
	 * @return number
	 */
	protected function _get_reccard_lists($start = 0) {
		// 如果有过期的分配就释放
		$condi['rnc_handletime'] = array(
			(startup_env::get('timestamp') - voa_d_cyadmin_recognition_namecard::REQUEST_EXPIRY_TIME),
			'<'
		);
		$condi['ca_id'] = array('', '<>');
		$lists = $this->_serv_namecard->fetch_by_conditions($condi);
		if (!empty($lists)) {
			foreach ($lists as $k => $v) {
				$this->_serv_namecard->update(array(
					'ca_id' => '',
					'rnc_handletime' => startup_env::get('timestamp')
				), $v['rnc_id']);
			}
		}

		// 如果刷新页面就清空一下之前分配的数据
		if ($start == 0) {
			$this->_serv_namecard->update_by_conditions(array(
				'ca_id' => '',
				'rnc_handletime' => startup_env::get('timestamp')
			), array('ca_id' => $this->_user['ca_id']));
		}

		$condi = array();
		$lists = array();
		if ($start) {
			$condi['rnc_id'] = array($start, '>');
		}
		$condi['ca_id'] = '';
		try {
			$this->_serv_namecard->begin();
			$lists = $this->_serv_namecard->fetch_by_conditions($condi, 0, voa_d_cyadmin_recognition_namecard::REQUEST_LIMIT, $order = "ASC", ' for update ');
			foreach ($lists as $key => $item) {
				$this->_serv_namecard->update(array(
					'ca_id' => $this->_user['ca_id'],
					'rnc_handletime' => startup_env::get('timestamp')
				), $item['rnc_id']);
			}
			$this->_serv_namecard->commit();
		} catch (Exception $e) {
			$this->_serv_namecard->rollback();
			$this->_error_message('askfor_new_failed', get_referer());
		}
		// 为数据列表注入 企业域名字段 _domain 和 图片绝对路径字段 _pictureurl
		uda::factory('voa_uda_cyadmin_enterprise_profile')->data_append_domain($lists);

		return $lists;
	}

	protected function _get_reccard_total_append($last_end_id) {
		$total = 0;
		if ($last_end_id) {
			$condi['rnc_id'] = array($last_end_id, '>');
			$condi['ca_id'] = '';
			$total = $this->_serv_namecard->count_by_conditions($condi);
		}

		return $total;
	}

	protected function _save_namecard($rnc_id, $data) {
		$ret = array('status' => 'ok', 'msg' => '');
		$item = $this->_serv_namecard->fetch($rnc_id);
		if (!empty($item)) {
			$info = array();
			foreach ($data as $key => $val) {
				if (trim($val) != '') {
					$info[$key] = $val;
				}
			}
			$item['rnc_namecardtext'] = serialize($info);
			$item['rnc_status'] = voa_d_cyadmin_recognition_namecard::STATUS_OVER;
			unset($item['rnc_id']);
			try {
				$this->_serv_namecard->begin();
				$this->_serv_namecard_backup->insert($item);
				$this->_serv_namecard->delete($rnc_id);
				$this->_serv_namecard->commit();
			} catch (Exception $e) {
				$this->_serv_namecard->rollback();
				$ret['status'] = 'no';
				$ret['msg'] = '操作不成功,数据库出错，请通知管理员。 ';
				//$this->_error_message('askfor_new_failed', get_referer());
			}
		}

		return $ret;
	}

	protected function _pull_reccard_back($rnc_id, $reason) {

		$status = ($reason == 'noclear' ? voa_d_cyadmin_recognition_namecard::STATUS_NO_IMAGE : voa_d_cyadmin_recognition_namecard::STATUS_NO_TYPE);
		$item = $this->_serv_namecard->fetch($rnc_id);
		if (!empty($item)) {
			$item['rnc_status'] = $status;
			unset($item['rnc_id']);
			try {
				$this->_serv_namecard->begin();
				$this->_serv_namecard_backup->insert($item);
				$this->_serv_namecard->delete($rnc_id);
				$this->_serv_namecard->commit();
			} catch (Exception $e) {
				$this->_serv_namecard->rollback();
				$this->_error_message('askfor_new_failed', get_referer());
			}
		}
	}

	/************   bill *****/
	protected function _get_recbill_end_id() {
		$condi['ca_id'] = '';
		$lists = $this->_serv_bill->fetch_by_conditions($condi, 0, 1, $order = 'DESC');
		$id = 0;
		if (!empty($lists)) {
			$id = $lists[0]['rb_id'];
		}

		return $id;
	}

	/**
	 * 根据条件计算总数
	 * @param  array $conditions
	 *  $conditions = array(
	 *      'field1' => '查询条件', // 运算符为 =
	 *      'field2' => array('查询条件', '查询运算符'),
	 *      'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *      ...
	 *  );
	 * @return number
	 */
	protected function _get_recbill_lists($start = 0) {
		// 如果有过期的分配就释放
		$condi['rb_handletime'] = array(
			(startup_env::get('timestamp') - voa_d_cyadmin_recognition_bill::REQUEST_EXPIRY_TIME),
			'<'
		);
		$condi['ca_id'] = array('', '<>');
		$lists = $this->_serv_bill->fetch_by_conditions($condi);
		if (!empty($lists)) {
			foreach ($lists as $k => $v) {
				$this->_serv_bill->update(array(
					'ca_id' => '',
					'rb_handletime' => startup_env::get('timestamp')
				), $v['rb_id']);
			}
		}

		// 如果刷新页面就清空一下之前分配的数据
		if ($start == 0) {
			$this->_serv_bill->update_by_conditions(array(
				'ca_id' => '',
				'rb_handletime' => startup_env::get('timestamp')
			), array('ca_id' => $this->_user['ca_id']));
		}

		$condi = array();
		$lists = array();
		if ($start) {
			$condi['rb_id'] = array($start, '>');
		}
		$condi['ca_id'] = '';
		try {
			$this->_serv_bill->begin();
			$lists = $this->_serv_bill->fetch_by_conditions($condi, 0, voa_d_cyadmin_recognition_bill::REQUEST_LIMIT, $order = "ASC", ' for update ');
			foreach ($lists as $key => $item) {
				$this->_serv_bill->update(array(
					'ca_id' => $this->_user['ca_id'],
					'rb_handletime' => startup_env::get('timestamp')
				), $item['rb_id']);
			}
			$this->_serv_bill->commit();
		} catch (Exception $e) {
			$this->_serv_bill->rollback();
			$this->_error_message('askfor_new_failed', get_referer());
		}

		// 为数据列表加入 企业域名字段 _domain 和 图片绝对路径字段 _pictureurl
		uda::factory('voa_uda_cyadmin_enterprise_profile')->data_append_domain($lists);

		return $lists;
	}


	protected function _get_recbill_total_append($last_end_id) {
		$total = 0;
		if ($last_end_id) {
			$condi['rb_id'] = array($last_end_id, '>');
			$condi['ca_id'] = '';
			$total = $this->_serv_bill->count_by_conditions($condi);
		}

		return $total;
	}

	protected function _save_bill($rb_id, $data) {
		$ret = array('status' => 'ok', 'msg' => '');
		$item = $this->_serv_bill->fetch($rb_id);
		if (!empty($item)) {
			$info = array();
			foreach ($data as $key => $val) {
				if (trim($val) != '') {
					$info[$key] = $val;
				}
			}
			$item['rb_billtext'] = serialize($info);
			$item['rb_status'] = voa_d_cyadmin_recognition_bill::STATUS_OVER;
			unset($item['rb_id']);
			try {
				$this->_serv_bill->begin();
				$this->_serv_bill_backup->insert($item);
				$this->_serv_bill->delete($rb_id);
				$this->_serv_bill->commit();
			} catch (Exception $e) {
				$this->_serv_bill->rollback();
				$ret = array('status' => 'no', 'msg' => '操作不成功，请通知管理员。');
				$this->_error_message('askfor_new_failed', get_referer());
			}
		}

		return $ret;
	}

	protected function _pull_bill_back($rb_id, $reason) {

		$status = ($reason == 'noclear' ? voa_d_cyadmin_recognition_bill::STATUS_NO_IMAGE : voa_d_cyadmin_recognition_bill::STATUS_NO_TYPE);
		$item = $this->_serv_bill->fetch($rb_id);
		if (!empty($item)) {
			$item['rb_status'] = $status;
			unset($item['rb_id']);
			try {
				$this->_serv_bill->begin();
				$this->_serv_bill_backup->insert($item);
				$this->_serv_bill->delete($rb_id);
				$this->_serv_bill->commit();
			} catch (Exception $e) {
				$this->_serv_bill->rollback();
				$this->_error_message('askfor_new_failed', get_referer());
			}
		}
	}
}
