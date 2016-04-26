<?php
/**
 * tj.php
 * 比对数据表
 * @uses php tool.php -n tj
 * $Author$
 * $Id$
 */
class voa_backend_tool_tj extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;
	// 时间戳
	protected $_ts;
	// 统计天数
	protected $_days = 7;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = &db::init($cfg);

		if ($this->_days >= 0) {
			$this->_ts = time() - $this->_days * 86400;
		} else {
			$this->_ts = 0;
		}

		/** 判断数据库是否存在 */
		$site_count = 0; // 站点总数
		$wx_site_count = 0; // 开启微信企业号的站点总数
		$member_count = 0; // 用户总数
		$plugin_count = 0; // 开启的应用总数
		$plugin_open_count = 0; // 开通了应用的企业数
		$plugin_data_count = array(); // 各应用发起新记录数
		$plugin_mem_do_count = array(); // 各应用中用户操作人次

		$this->_db->query('USE vchangyi_admincp');
		$q = $this->_db->query("SELECT `ep_id` FROM cy_enterprise_profile ORDER BY ep_id DESC LIMIT 1");
		$last_ep_id = $this->_db->result($q);

		// 应用信息
		$plugins = array();

		$site_count = $last_ep_id - 10002;

		// 开启某个应用的企业数
		$open_plugin_qy_count = array();

		for ($i = 10002; $i < $last_ep_id; ++ $i) {
			if ($i == 10002) {
				continue;
			}
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

				// 读取所有开通过的插件
				$opened = false;
				$q = $this->_db->query('SELECT * FROM oa_common_plugin WHERE cp_available>0 AND cp_available<255');
				while ($row = $this->_db->fetch_array($q)) {

					if (!isset($plugins[$row['cp_identifier']])) {
						$plugins[$row['cp_identifier']] = $row;
					}

					if (!isset($plugin_data_count[$row['cp_identifier']])) {
						$plugin_data_count[$row['cp_identifier']] = 0;
					}
					if (!isset($plugin_mem_do_count[$row['cp_identifier']])) {
						$plugin_mem_do_count[$row['cp_identifier']] = 0;
					}

					$plugin_count ++;
					// 开通过应用的企业计数 +1
					if (false == $opened) {
						$opened = true;
						$plugin_open_count ++;
					}

					// 启用某个应用的企业数
					if (!isset($open_plugin_qy_count[$row['cp_identifier']])) {
						$open_plugin_qy_count[$row['cp_identifier']] = 0;
					}
					if ($row['cp_available'] == voa_d_oa_common_plugin::AVAILABLE_OPEN) {
						$open_plugin_qy_count[$row['cp_identifier']]++;
					}

					// 方法是否存在
					$func = '_ct_'.$row['cp_identifier'];
					if (!method_exists($this, $func)) {
						continue;
					}

					$count = 0;
					$count_do = 0;
					$this->$func($count, $count_do);

					$plugin_data_count[$row['cp_identifier']] += $count;
					$plugin_mem_do_count[$row['cp_identifier']] += $count_do;
				}
			} catch (Exception $e) {
				if (stripos($e->getMessage(), 'Unknown database ') === false) {
					echo "\nep_{$i}: ".($e->getMessage())."\n";
				}
				continue;
			}
		}

		$title = "日期\t站点总数\t已开启微信\t总用户数\t应用总数\t已开启应用总数\t应用主数据数\t应用数据操作数\n";
		$content = date('Y-m-d', startup_env::get('timestamp'))."\t".$site_count."\t".$wx_site_count."\t".$member_count."\t".$plugin_count."\t".$plugin_open_count;
		$total_dc = 0;
		$total_dc_all = array();
		$total_ddc = 0;
		$total_ddc_all = array();

		arsort($plugin_data_count);
		foreach ($plugin_data_count as $_k => $_v) {
			$total_dc += $_v;
			$total_dc_all[] = "{$plugins[$_k]['cp_name']}:{$_v}";
		}
		unset($_k, $_v);

		arsort($plugin_mem_do_count);
		foreach ($plugin_mem_do_count as $_k => $_v) {
			$total_ddc += $_v;
			$total_ddc_all[] = "{$plugins[$_k]['cp_name']}:{$_v}";
		}
		unset($_k, $_v);

		// 开通某个应用的企业数
		$open_plugin_qy_count_all = array();
		arsort($open_plugin_qy_count);
		foreach ($open_plugin_qy_count as $_k => $_v) {
			$open_plugin_qy_count_all[] = "{$plugins[$_k]['cp_name']}:{$_v}";
		}
		unset($_k, $_v);

		$content .= "\t".$total_dc."\t".$total_ddc."\n";
		$content .= "应用主记录数: ".implode("; ", $total_dc_all)."\n";
		$content .= "应用总记录数: ".implode("; ", $total_ddc_all)."\n";
		$content .= "开启应用的企业总数: ".implode('; ', $open_plugin_qy_count_all)."\n";

		$output = $title.$content;
		echo $output;
	}

	/**
	 * 统计日报
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_dailyreport(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport WHERE dr_created>{$this->_ts}");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport_post WHERE drp_created>{$this->_ts} AND drp_status>1");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计签到
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_sign(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_record WHERE sr_created>{$this->_ts}");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_location WHERE sl_created>{$this->_ts}");
		$count = $this->_db->result($q) + $count;

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_plead WHERE sp_created>{$this->_ts}");
		$count = $this->_db->result($q) + $count;

		// 操作数
		$count_do = $count;

		return true;
	}

	/**
	 * 统计审批
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_askfor(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor WHERE af_created>{$this->_ts}");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_reply WHERE afr_created>{$this->_ts}");
		$count_do = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_comment WHERE afc_created>{$this->_ts}");
		$count_do = $this->_db->result($q) + $count_do;

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_proc WHERE afp_created>{$this->_ts} AND afp_status>1");
		$count_do = $this->_db->result($q) + $count_do;

		return true;
	}

	/**
	 * 统计请假
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_askoff(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff WHERE ao_created>{$this->_ts}");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_post WHERE aopt_created>{$this->_ts}");
		$count_do = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_proc WHERE aopc_created>{$this->_ts} AND aopc_status>1");
		$count_do = $this->_db->result($q) + $count_do;

		return true;
	}

	/**
	 * 统计报销
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_reimburse(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse WHERE rb_created>{$this->_ts}");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_bill WHERE rbb_created>{$this->_ts}");;
		$count = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_proc WHERE rbpc_created>{$this->_ts} AND rbpc_status>1");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计订会议室
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_meeting(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting WHERE mt_created>{$this->_ts}");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting_mem WHERE mm_created>{$this->_ts} AND mm_status>1");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计备忘
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_vnote(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$count = $this->_db->result_first("SELECT COUNT(*) FROM oa_vnote WHERE vn_created>{$this->_ts}");
		// 操作数
		$count_do = $this->_db->result_first("SELECT COUNT(*) FROM oa_vnote_mem WHERE vnm_created>{$this->_ts}") + $count;

		return true;
	}

	/**
	 * 统计会议记录
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_minutes(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes WHERE mi_created>{$this->_ts}");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes_post WHERE mip_created>{$this->_ts}");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计任务
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_project(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_project WHERE p_created>{$this->_ts}");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_project_proc WHERE pp_created>{$this->_ts} AND pp_status>1");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计巡店
	 * @param int $count 发起记录数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_inspect(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect WHERE ins_created>{$this->_ts}");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect_score WHERE isr_created>{$this->_ts}");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计营销CRM
	 * @param number $count 备注数
	 * @param number $count_do 分享数
	 * @return boolean
	 */
	protected function _ct_travel(&$count = 0, &$count_do = 0) {

		$count = (int)$count;
		$count_do = (int)$count_do;

		// 备注数
		$q = $this->_db->query("SELECT COUNT(`crk_id`) FROM `oa_travel_customer_remark` WHERE `created`>{$this->_ts}");
		$count = $this->_db->result($q);

		// 分享数
		$q = $this->_db->query("SELECT COUNT(`tsc_id`) FROM `oa_travel_share_count` WHERE `created`>{$this->_ts}");
		$count_do = $this->_db->result($q);

		return true;
	}

	/**
	 * 统计新闻公告
	 * @param number $count 新闻发表数
	 * @param number $count_do 总的阅读数
	 */
	protected function _ct_news(&$count = 0, &$count_do = 0) {

		// 新闻发表数
		$count = (int)$this->_db->result_first("SELECT COUNT(`ne_id`) FROM `oa_news` WHERE `created`>{$this->_ts}");

		// 总阅读数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`nre_id`) FROM `oa_news_read` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计同事社区
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_thread(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;

		// 主题数
		$count = (int)$this->_db->result_first("SELECT COUNT(`tid`) FROM `oa_thread` WHERE `created`>{$this->_ts}");
		// 评论 + 评论回复数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`pid`) FROM `oa_thread_post` WHERE `created`>{$this->_ts}");
		$count_do = $count_do + (int)$this->_db->result_first("SELECT COUNT(`prid`) FROM `oa_thread_post_reply` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计通讯录
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_addressbook(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;

		// 通讯录总人数
		$count = (int)$this->_db->result_first("SELECT COUNT(`m_uid`) FROM `oa_member` WHERE `m_created`>{$this->_ts}");
		// 通讯录近期更新人数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`m_uid`) FROM `oa_member` WHERE `m_updated`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计移动派单
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_workorder(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 派单总数
		$count = (int)$this->_db->result_first("SELECT COUNT(`woid`) FROM `oa_workorder` WHERE `created`>{$this->_ts}");
		// 工单操作数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`wologid`) FROM `oa_workorder_log` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计培训
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_train(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 文章总数
		$count = (int)$this->_db->result_first("SELECT COUNT(`ta_id`) FROM `oa_train_article` WHERE `created`>{$this->_ts}");
		// 阅读情况
		$count_do = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_train_article_member` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计陈列
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_showroom(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 文章总数
		$count = (int)$this->_db->result_first("SELECT COUNT(`ta_id`) FROM `oa_showroom_article` WHERE `created`>{$this->_ts}");
		// 阅读情况
		$count_do = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_showroom_article_member` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计投票调研
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_nvote(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 投票数
		$count = (int)$this->_db->result_first("SELECT COUNT(`id`) FROM `oa_nvote` WHERE `created`>{$this->_ts}");
		// 投票次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`id`) FROM `oa_nvote_mem` WHERE `created`>{$this->_ts}");

		return true;
	}

	/**
	 * 统计活动报名
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_activity(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 活动数
		$count = (int)$this->_db->result_first("SELECT COUNT(`acid`) FROM `oa_activity` WHERE `created`>{$this->_ts}");
		// 报名次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`apid`) FROM `oa_activity_partake` WHERE `created`>{$this->_ts}");

		$count_do += $count;
		return true;
	}

	/**
	 * 统计快递助手
	 * @param number $count
	 * @param number $count_do
	 * @return boolean
	 */
	protected function _ct_express(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 快递次数
		$count = (int)$this->_db->result_first("SELECT COUNT(`eid`) FROM `oa_express` WHERE `created`>{$this->_ts}");
		// 快递响应次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`mid`) FROM `oa_express_mem` WHERE `created`>{$this->_ts}");

		$count_do += $count;
		return true;
	}

}
