<?php
/**
 * 报销基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_base extends voa_c_frontend_base {
	/** 订单状态 */
	const BILL_ALL = 0;
	const BILL_NORMAL = 1;
	const BILL_USED = 2;
	/** 日期别称 */
	public static $s_alias_day = array('今天', '昨天', '前天');

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '报销');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.reimburse.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');
			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
			return true;
		}

		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		/** 加载提示语言 */
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	/**
	 * 获取配置
	 */
	public static function fetch_cache_reimburse_setting() {

		$serv = &service::factory('voa_s_oa_reimburse_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['rbs_type']) {
				$arr[$v['rbs_key']] = unserialize($v['rbs_value']);
			} else {
				$arr[$v['rbs_key']] = $v['rbs_value'];
			}
		}

		self::_check_agentid($arr, 'reimburse');
		return $arr;
	}

	/**
	 * 根据uid读取清单列表
	 * @param array $bills
	 * @param int $uid
	 * @param int $start
	 * @param int $limit
	 * @param int $status 状态, 0: 全部; 1:未使用; 2:已使用
	 * @return boolean
	 */
	protected function _get_bill_by_uid(&$bills, $uid, $start = 0, $limit = 0, $status = 0) {
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		$serv = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bills = $serv->fetch_by_uid($uid, $start, $limit, $status);
		if (!$fmt->reimburse_bill_list($bills)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 根据订单id读取附件信息
	 * @param array $attachs
	 * @param mix $at_ids
	 * @return boolean
	 */
	protected function _get_attach_by_at_id(&$attachs, $at_ids) {
		if (empty($at_ids)) {
			return true;
		}

		$fmt = &uda::factory('voa_uda_frontend_attachment_format');
		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attachs = $serv->fetch_by_ids($at_ids);
		if (!$fmt->format_list($attachs)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 根据报销id读取回复
	 * @param array $posts
	 * @param int $rb_id
	 * @return boolean
	 */
	protected function _get_post_by_rb_id(&$posts, $rb_id) {
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		$serv = &service::factory('voa_s_oa_reimburse_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv->fetch_by_rb_id($rb_id);
		if (!$fmt->reimburse_post_list($posts)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 根据报销id读取进度信息
	 * @param array $procs
	 * @param int $rb_id
	 * @return boolean
	 */
	protected function _get_proc_by_rb_id(&$procs, $rb_id) {
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		$serv = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv->fetch_by_rb_id($rb_id);
		if (!$fmt->reimburse_proc_list($procs)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 根据报销id读取订单列表
	 * @param array $bills
	 * @param int $rb_id
	 * @return boolean
	 */
	protected function _get_bill_by_rb_id(&$bills, $rb_id) {
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		$serv_rbs = &service::factory('voa_s_oa_reimburse_bill_submit', array('pluginid' => startup_env::get('pluginid')));
		$bills = $serv_rbs->fetch_by_rb_id($rb_id);
		if (!$fmt->reimburse_bill_list($bills)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 根据报销id读取报销信息
	 * @param array $reimburse
	 * @param int $rb_id
	 * @return boolean
	 */
	protected function _get_reimburse(&$reimburse, $rb_id) {
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$reimburse = $serv->fetch_by_id($rb_id);

		/** 判断权限 */
		if (empty($rb_id)) {
			$this->_error_message('该报销不存在或已被删除');
			return false;
		}

		/** 过滤 */
		if (!$fmt->reimburse($reimburse)) {
			$this->_error_message($fmt->error);
			return false;
		}

		return true;
	}

	/**
	 * 取类型
	 * @param int $type 报销单据类型
	 * @param string $msg 消息信息
	 */
	public static function get_type(&$type, $msg) {
		/** 类型关键词 */
		$p_sets['typekey'] = array(
			2 => "吃饭|请客|招待|腐败|喝茶",
			1 => "打车|打的|地铁|公交|火车|飞机|轮船"
		);

		/** 从消息中取类型值 */
		if (preg_match("/(".implode('|', $p_sets['typekey']).")/i", $msg, $matches)) {
			foreach ($p_sets['typekey'] as $k => $v) {
				if (false === stripos($v, $matches[1])) {
					continue;
				}

				$type = $k;
			}
		}

		return true;
	}

	/**
	 * 取花费
	 * @param float $expend 花费值
	 * @param string $msg 消息信息
	 * @return boolean
	 */
	public static function get_expend(&$expend, $msg) {
		/** 花费关键词 */
		$expend_key = "￥|¥|花费|花了|支付|支出|用了|花";

		/** 根据单位找花费 */
		if (preg_match("/(\d+\.?\d*)(元|块)/", $msg, $match)) {
			$expend = $match[1];
			return true;
		}

		/** 从消息中取花费 */
		if (preg_match("/(".$expend_key.")(.*?)(\d+\.?\d*)/", $msg, $match)) {
			$expend = $match[3];
			return true;
		}

		return false;
	}

	/**
	 * 取时间戳
	 * @param int $time 时间戳
	 * @param string $msg 消息信息
	 * @return boolean
	 */
	public static function get_timestamp(&$time, $msg) {
		/** 时间关键词 */
		$day_key = implode('|', self::$s_alias_day)."|大前天";

		/** 先取日期 */
		list($y, $n, $j, $w) = explode('-', rgmdate($time, 'Y-n-j-w'));
		/** 完全日期 */
		if (preg_match("/(20\d{2})(-|年|\/|.)(\d{2})(-|月|\/|.)(\d{2})/", $msg, $match)) {
			$ts = rstrtotime('20'.$match[1].'-'.$match[2].'-'.$match[3].' 00:00:00');
			if (0 < $ts) {
				$time = $ts;
			}
		}

		/** 月/日 */
		if (0 >= $ts && preg_match("/(\d{2})(-|月|\/|.)(\d{2})/", $msg, $match)) {
			$ts = rstrtotime($y.'-'.$match[1].'-'.$match[2].' 00:00:00');
			if (0 < $ts) {
				$time = $ts;
			}
		}

		/** x日 */
		if (0 >= $ts && preg_match("/(\d{2})(日|号)/", $msg, $match)) {
			if ($j < $match[1]) {
				$_n = $n - 1;
			}

			if (0 >= $_n) {
				$_y = $y - 1;
			}

			$ts = rstrtotime($_y.'-'.$_n.'-'.$match[1].' 00:00:00');
			if (0 < $ts) {
				$time = $ts;
			}
		}

		/** 上周x/周x/星期x */
		$weeks = array('一', '二', '三', '四', '五', '六', '日', '天', '1', '2', '3', '4', '5', '6', '7');
		if (0 >= $ts && preg_match("/(上星期|上周|周|星期)(".implode('|', $weeks).")/", $msg, $match)) {
			switch ($match[2]) {
				case '一':
				case '1':
					$_w = 1;
					break;
				case '二':
				case '2':
					$_w = 2;
					break;
				case '三':
				case '3':
					$_w = 3;
					break;
				case '四':
				case '4':
					$_w = 4;
					break;
				case '五':
				case '5':
					$_w = 5;
					break;
				case '六':
				case '6':
					$_w = 6;
					break;
				case '日':
				case '7':
				case '天':
					$_w = 7;
					break;
			}

			$__w = 0 == $w ? 7 : $w;
			if ('上周' == $match[1] || '上星期' == $match[1] || $__w < $_w) {
				$days = $__w - $_w + 7;
				$ts = $time - $days * 86400;
			} else {
				$ts = $time - ($__w - $_w) * 86400;
			}

			if (0 < $ts) {
				$time = $ts;
			}
		}

		if (0 >= $ts && preg_match("/(".$day_key.")/", $msg, $match)) {
			switch ($match[1]) {
				case "昨天":$time -= 86400; break;
				case "前天":$time -= 86400 * 2; break;
				case "大前天":$time -= 86400 * 3; break;
				default: break;
			}
		}

		/** 取时间 */
		$time_key = "凌晨|早上|上午|中午|下午|傍晚|晚上|半夜";
		$hi = '';
		if (preg_match("/(".$time_key.")?(\d+)(点|:|：|时)(\d*)/", $msg, $match)) {
			$h = (int)$match[2];
			$i = (int)$match[4];
			$i = 59 < $i || 0 > $i ? '00' : str_pad($i, 2, '0', STR_PAD_LEFT);
			switch ($match[1]) {
				case "凌晨": break;
				case "半夜": break;
				case "早上": break;
				case "上午": break;
				case "中午": if (6 > $h) { $h += 12; } break;
				case "下午":
				case "傍晚":
				case "晚上": if (12 > $h) { $h += 12; } break;
				default: break;
			}

			$h = 23 < $h || 0 > $h ? null : str_pad($h, 2, '0', STR_PAD_LEFT);
		}

		if (null != $h) {
			$hi = $h.':'.$i;
		}

		$ymdhi = rgmdate($time, 'Y-m-d ').(empty($hi) ? rgmdate($time, 'H:i') : $hi).':00';
		$time = rstrtotime($ymdhi);
		return true;
	}

	/**
	 * 切分单据消息, 分离出时间/类型/花费
	 * @param int &$time 时间戳
	 * @param int &$type 类型
	 * @param int &$expend 花费
	 * @param string $msg 消息详情
	 */
	public static function parse_msg(&$time, &$type, &$expend, $msg) {
		self::get_type($type, $msg);
		self::get_expend($expend, $msg);
		self::get_timestamp($time, $msg);

		$expend *= 100;

		return true;
	}

	/** 显示操作菜单 */
	public static function show_menu($data, $plugin) {
		$serv = voa_wxqy_service::instance();
		/** 取草稿内容 */
		$content = $data['content'];
		$serv_dr = &service::factory('voa_s_oa_reimburse_draft', array('pluginid' => startup_env::get('pluginid')));
		$draft = $serv_dr->get_by_openid($data['from_user_name']);

		/** 更新草稿内容 */
		if (empty($draft)) {
			$serv_dr->insert(array(
				'm_openid' => $data['from_user_name'],
				'rbd_message' => $content
			));
		} else {
			$serv_dr->update(array('rbd_message' => $content), array('m_openid' => $data['from_user_name']));
		}

		/** 整理段落序号 */
		$msg = str_replace(array(' ', '　'), '', $content);
		$time = startup_env::get('timestamp');
		$type = 0;
		$expend = 0;
		self::parse_msg($time, $type, $expend, $msg);

		/** 报销单据入库 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$mem = $serv_m->fetch_by_openid($data['from_user_name']);
		/** 如果用户不存在, 则 */
		if (empty($mem)) {
			return '';
		}

		$serv_bill = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bill = array(
			'm_uid' => $mem['m_uid'],
			'm_username' => $mem['m_username'],
			'rbb_type' => $type,
			'rbb_time' => $time,
			'rbb_expend' => $expend,
			'rbb_reason' => $content
		);
		$bill['rbb_id'] = $serv_bill->insert($bill, true);

		/** 过滤 */
		$fmt = uda::factory('voa_uda_frontend_reimburse_format');
		$fmt->reimburse_bill($bill);

		/** 读取报销配置 */
		$sets = voa_h_cache::get_instance()->get('plugin.reimburse.setting', 'oa');
		/** 计算相关的天数 */
		/*$ymd = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		$ts = rstrtotime($ymd.' 00:00:00');
		$day_index = $ts > $time ? (int)ceil(($ts - $time) / 86400) : 0;*/

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/reimburse/bill/edit/'.$bill['rbb_id'], $plugin['cp_pluginid']));
		$ret = "已记录此明细\n"
			 . "时间：".str_replace('&nbsp;', '', $bill['_time_u'])."\n"
			 . "类别：".(empty($type) ? '未知' : $sets['types'][$type])."\n"
			 . "事由：{$content}\n"
			 . "金额：".(0 < $bill['_expend'] ? $bill['_expend'].'元' : '未提及')."\n"
			 . "===操作===\n"
			 . ' <a href="'.$viewurl.'">编辑明细</a>';

		return $ret;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $rb_id 请假信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $rb_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/reimburse/view/'.$rb_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $a_uid 接收人uid
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($a_uid = 0, $cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_reimburse_draft', array('pluginid' => startup_env::get('pluginid')));
		$rbd_id = (int)$this->request->get('rbd_id');
		if (0 < $rbd_id) {
			$serv->update(array(
				'rbd_message' => '',
				'rbd_a_uid' => $a_uid,
				'rbd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('rbd_id' => $rbd_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'rbd_a_uid' => $a_uid,
				'rbd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_reimburse_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('rbd_id', $this->_draft['rbd_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['rbd_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['rbd_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['rbd_cc_uid'])) {
			$uids = explode(',', $this->_draft['rbd_cc_uid']);
		}

		if (!empty($this->_draft['rbd_a_uid'])) {
			$uids[] = $this->_draft['rbd_a_uid'];
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		/** 输出接收人 */
		$ret = array();
		if (!empty($this->_draft['rbd_a_uid'])) {
			$ret['accepter'] = $users[$this->_draft['rbd_a_uid']];
			unset($users[$this->_draft['rbd_a_uid']]);
		}

		$ret['ccusers'] = $users;
		$ret['message'] = $msg;
		return true;
	}

	/**
	 * 附件处理
	 * @param array $attachment 附件信息
	 * @param array $data 消息信息
	 * @param array $plugin 插件信息
	 */
	public static function wx_attach($attachment, $data, $plugin) {
		/** 取站点配置信息 */
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		/** 名片信息入库 */
		$serv_rbb = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$rbb_id = $serv_rbb->insert(array(
			'at_id' => $attachment['at_id'],
			'rbb_status' => voa_d_oa_reimburse_bill::STATUS_REMOVE
		), true);

		/** 微信消息 */
		$ret = "发票识别中, 请稍等";

		return $ret;
	}
}
