<?php
/**
 * tj.php
 * 比对数据表
 * @uses php tool.php -n tjz -days 7
 * $Author$
 * $Id$
 */
class voa_backend_cron_tjz extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;
	// 时间戳
	protected $_ts;
	// 统计天数
	protected $_days = 7;
	// 统计时间段
	protected $_mts = array();

	public function __construct($opts) {

		parent::__construct();
		if (isset($opts['days'])) {
			$opts['days'] = (int)$opts['days'];
		}

		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = &db::init($cfg);

		if (!empty($this->__opts['days'])) {
			$this->_days = $this->__opts['days'];
		}

		$this->_ts = time() - $this->_days * 86400;

		$this->_mts = array(
			9 => array(strtotime('2014-09-01'), strtotime('2014-10-01')),
			10 => array(strtotime('2014-10-01'), strtotime('2014-11-01')),
			11 => array(strtotime('2014-11-01'), strtotime('2014-12-01')),
			12 => array(strtotime('2014-12-01'), strtotime('2015-01-01')),
			1 => array(strtotime('2015-01-01'), strtotime('2015-02-01')),
			2 => array(strtotime('2015-02-01'), strtotime('2015-03-01')),
			3 => array(strtotime('2015-03-01'), strtotime('2015-04-01')),
			4 => array(strtotime('2015-04-01'), strtotime('2015-05-01'))
		);

		/** 判断数据库是否存在 */
		$site_count = 0; // 站点总数
		$wx_site_count = 0; // 开启微信企业号的站点总数
		$site_active_week = 0; // 周活跃企业
		$member_count = 0; // 用户总数
		$plugin_count = 0; // 开启的应用总数
		$plugin_open_count = 0; // 开通了应用的企业数
		$plugin_data_count = array(
			9 => array(), 10 => array(), 11 => array(), 12 => array(),
			1 => array(), 2 => array(), 3 => array(), 4 => array()
		); // 各应用发起新记录数
		$plugin_mem_do_count = array(
			9 => array(), 10 => array(), 11 => array(), 12 => array(),
			1 => array(), 2 => array(), 3 => array(), 4 => array()
		); // 各应用中用户操作人次
		$member_active = 0; // 用户活跃数

		$this->_db->query('USE vchangyi_admincp');
		$q = $this->_db->query("SELECT `ep_id` FROM cy_enterprise_profile ORDER BY ep_id DESC LIMIT 1");
		$last_ep_id = $this->_db->result($q);

		$site_count = $last_ep_id - 10002;

		for ($i = 10002; $i < $last_ep_id; ++ $i) {
			try {
				$this->_db->query('USE ep_'.$i);

				// by Deepseath 20141210
				$wxqy_status = "'".voa_d_oa_common_setting::WXQY_MANUAL."', '".voa_d_oa_common_setting::WXQY_AUTH."'";
				// 读取微信标识
				$q = $this->_db->query("SELECT COUNT(*) FROM oa_common_setting WHERE cs_key='ep_wxqy' AND cs_value IN ({$wxqy_status})");
				if (0 >= $this->_db->result($q)) {
					continue;
				}

				// 微信站点总数 +1
				$wx_site_count ++;

				// 读取用户数
				$q = $this->_db->query("SELECT COUNT(*) FROM oa_member WHERE m_status<4");
				if ($c = $this->_db->result($q)) {
					$member_count += $c;
				}

				// 活跃数
				$q = $this->_db->query("SELECT COUNT(*) FROM oa_member WHERE m_status<4 AND m_updated>{$this->_ts}");
				if ($cha = $this->_db->result($q)) {
					$member_active += $cha;
				}

				// 读取所有开通过的插件
				$opened = false;
				$q = $this->_db->query('SELECT * FROM oa_common_plugin WHERE cp_available>0 AND cp_available<255');
				while ($row = $this->_db->fetch_array($q)) {
					$plugin_count ++;
					// 开通过应用的企业计数 +1
					if (false == $opened) {
						$opened = true;
						$plugin_open_count ++;
					}

					// 方法是否存在
					$func = '_ct_'.$row['cp_identifier'];
					if (!method_exists($this, $func)) {
						continue;
					}


					$count = array();
					$count_do = array();
					$this->$func($count, $count_do);
					foreach ($count as $_k => $_v) {
						$plugin_data_count[$_k][$row['cp_identifier']] += $_v;
						$plugin_mem_do_count[$_k][$row['cp_identifier']] += $_v;
					}
					//$plugin_data_count[$row['cp_identifier']] += $count;
					//$plugin_mem_do_count[$row['cp_identifier']] += $count_do;
					if (0 < $count + $count_do) {
						$site_active_week ++;
					}
				}
			} catch (Exception $e) {
				continue;
			}
		}

		//$title = "日期\t站点总数\t已开启微信\t周活跃企业数\t总用户数\t已开启应用总数\t已开启应用的企业总数\t应用总记录数/使用人次\t各应用记录数/使用人次\n";
		$content = "日期:".date('Y-m-d', startup_env::get('timestamp'))."\n"
				 . "站点总数:".$site_count."\n"
				 . "已开启微信:".$wx_site_count."\n"
				 . "周活跃企业数:".$site_active_week."\n"
				 . "总用户数:".$member_count."\n"
				 . "活跃用户数:".$member_active."\n"
				 . "已开启应用总数:".$plugin_count."\n"
				 . "已开启应用的企业总数:".$plugin_open_count."\n";
		$total_dc = 0;
		$total_dc_all = array();
		$total_ddc = 0;
		$total_ddc_all = array();
		foreach ($plugin_data_count as $_k => $_v) {
			//$total_dc += $_v;
			//$total_dc_all[] = "{$_k}:{$_v}";
		}

		foreach ($plugin_mem_do_count as $_k => $_v) {
			//$total_ddc += $_v;
			//$total_ddc_all[] = "{$_k}:{$_v}";
		}

		$content .= "应用发起数/记录数:".$total_dc."/".$total_ddc."\n"
				 .  "各应用发起数:".implode("; ", $total_dc_all)."\n"
				 .  "各应用记录数:".implode("; ", $total_ddc_all)."\n";

		$content .= "\nplugin data count: ".var_export($plugin_data_count, true);
		$content .= "\nplugin mem do count: ".var_export($plugin_mem_do_count, true);
		$file = APP_PATH."/data/tj-".$this->_days.".log";
		if (!file_exists($file)) {
			//$content = $title.$content;
		}

		rfwrite($file, $content, 'a+');
	}

	protected function _ct_thread(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_thread WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_thread_likes WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_thread_post WHERE first=0 AND updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_activity(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_activity WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_activity_partake WHERE created<updated AND updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_activity_nopartake WHERE created<updated AND updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count_do[$_k];
		}

		return true;
	}

	protected function _ct_nvote(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_nvote WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_nvote_mem_option WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_news(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;

		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_news WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_news_read WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_train(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;

		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_train_article WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_train_article_member WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_showroom(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;

		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_showroom_article WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_showroom_article_member WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_workorder(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_workorder WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_workorder_receiver WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	protected function _ct_travel(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_goods_data WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_customer_data WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_travel_customer_goods WHERE created>{$_ts[0]} AND created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_travel_customer_remark WHERE updated>{$_ts[0]} AND updated<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q) + $count[$_k];
			$count_do += $count;
		}

		return true;
	}

	/**
	 * 统计日报
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_dailyreport(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport WHERE dr_created>{$_ts[0]} AND dr_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport_post WHERE drp_created>{$_ts[0]} AND drp_created<{$_ts[1]} AND drp_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计签到
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_sign(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		// 发起数
		foreach ($this->_mts as $_k => $_ts) {
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_record WHERE sr_created>{$_ts[0]} AND sr_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_location WHERE sl_created>{$_ts[0]} AND sl_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q) + $count[$_k];

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_plead WHERE sp_created>{$_ts[0]} AND sp_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$count_do[$_k] = $count;
		}

		return true;
	}

	/**
	 * 统计审批
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_askfor(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor WHERE af_created>{$_ts[0]} AND af_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_reply WHERE afr_created>{$_ts[0]} AND afr_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_comment WHERE afc_created>{$_ts[0]} AND afc_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count_do[$_k];

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_proc WHERE afp_created>{$_ts[0]} AND afp_created<{$_ts[1]} AND afp_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count_do[$_k];
		}

		return true;
	}

	/**
	 * 统计请假
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_askoff(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff WHERE ao_created>{$_ts[0]} AND ao_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_post WHERE aopt_created>{$_ts[0]} AND aopt_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_proc WHERE aopc_created>{$_ts[0]} AND aopc_created<{$_ts[1]} AND aopc_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count_do[$_k];
		}

		return true;
	}

	/**
	 * 统计报销
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_reimburse(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse WHERE rb_created>{$_ts[0]} AND rb_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_bill WHERE rbb_created>{$_ts[0]} AND rbb_created<{$_ts[1]}");;
			$count[$_k] = $this->_db->result($q) + $count[$_k];

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_proc WHERE rbpc_created>{$_ts[0]} AND rbpc_created<{$_ts[1]} AND rbpc_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计订会议室
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_meeting(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting WHERE mt_created>{$_ts[0]} AND mt_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting_mem WHERE mm_created>{$_ts[0]} AND mm_created<{$_ts[1]} AND mm_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计备忘
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_vnote(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_vnote WHERE vn_created>{$_ts[0]} AND vn_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_vnote_mem WHERE vnm_created>{$_ts[0]} AND vnm_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计会议记录
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_minutes(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes WHERE mi_created>{$_ts[0]} AND mi_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes_post WHERE mip_created>{$_ts[0]} AND mip_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计任务
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_project(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_project WHERE p_created>{$_ts[0]} AND p_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_project_proc WHERE pp_created>{$_ts[0]} AND pp_created<{$_ts[1]} AND pp_status>1");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

	/**
	 * 统计巡店
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_inspect(&$count, &$count_do) {

		$count = (array)$count;
		$count_do = (array)$count_do;
		foreach ($this->_mts as $_k => $_ts) {
			// 发起数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect WHERE ins_created>{$_ts[0]} AND ins_created<{$_ts[1]}");
			$count[$_k] = $this->_db->result($q);

			// 操作数
			$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect_score WHERE isr_created>{$_ts[0]} AND isr_created<{$_ts[1]}");
			$count_do[$_k] = $this->_db->result($q) + $count[$_k];
		}

		return true;
	}

}
