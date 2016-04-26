<?php
/**
 * yztjsite.php
 * 统计指定站点的数据
 * @uses php tool.php -n yztjsite -dbname ep_10002 -sdate 2016-01-01 00:00:00 -edate 2106-01-01 00:00:00
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_backend_tool_yztjsite extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	/** 数据库连接 */
	protected $_db;
	/** 时间戳 */
	protected $_s_ts = 0;
	protected $_e_ts = 0;
	/** 统计天数 */
	protected $_days = 0;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		ini_set('memory_limit','1024M');

		// 连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = db::init($cfg);
		// 统计起始时间
		if (!empty($this->__opts['sdate'])) {
			$this->_s_ts = rstrtotime($this->__opts['sdate']);
		}
		if (!empty($this->__opts['edate'])) {
			$this->_e_ts = rstrtotime($this->__opts['edate']);
		}

		if (!empty($this->__opts['days'])) {
			$this->_e_ts = time();
			$this->_s_ts = $this->_e_ts - $this->__opts['days'] * 86400;
		}

		// 如果是指定了 ep_id
		$ep_where = '';
		if (! empty($this->__opts['dbname'])) {
			$ep_where = " WHERE `ep_id`=" . substr($this->__opts['dbname'], 3);
		} elseif (! empty($this->__opts['days'])) {
			$days = (int)$this->__opts['days'];
			$ep_where = " WHERE `ep_created`>" . (startup_env::get('timestamp') - (86400 * $days));
		}
		$ep_where = " WHERE 1";

		// 最后一个站点库名
		$this->_db->query('USE vchangyi_admincp');
		$q = $this->_db->query("SELECT `ep_id` FROM cy_enterprise_profile ORDER BY ep_id DESC LIMIT 1");
		$last_ep_id = $this->_db->result($q);
		// 全部企业信息
		$ep_list = array();
		$start_ep_id = 0;
		$q = $this->_db->query("SELECT * FROM cy_enterprise_profile{$ep_where} ORDER BY `ep_id` ASC");
		while ($row = $this->_db->fetch_array($q)) {
			if (! $start_ep_id || $start_ep_id > $row['ep_id']) {
				$start_ep_id = $row['ep_id'];
			}

			$ep_list[$row['ep_id']] = $row;
		}
		unset($row, $q);

		$q = $this->_db->query("SELECT * FROM cy_common_adminer");
		$adminers = array();
		while ($row = $this->_db->fetch_array($q)) {
			$adminers[$row['ca_id']] = $row;
		}

		// 输出的数据
		$output = array();
		// 全部应用列表
		$plugin_list = array(
			'invite' => array('cp_name' => '邀请人员', 'cp_identifier' => 'invite'),
			'chatgroup' => array('cp_name' => '同事聊天', 'cp_identifier' => 'chatgroup')
		);
		try {
			$this->_db->query('USE ep_10002');
			// 读取体验站应用以获取全部应用信息
			$q = $this->_db->query('SELECT * FROM oa_common_plugin WHERE cp_available>0 AND cp_available<255 ORDER BY `cp_pluginid` ASC');
			while ($row = $this->_db->fetch_array($q)) {
				$plugin_list[$row['cp_identifier']] = $row;
			}
			unset($q, $row);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}

		if (!empty($this->__opts['dbname'])) {
			// 单个站点
			try {
				if (strpos($this->__opts['dbname'], 'ep_') === false) {
					$dbname = 'ep_'.$this->__opts['dbname'];
				} else {
					$dbname = $this->__opts['dbname'];
				}
				$this->_db->query('USE '.$dbname);
				$result = array();
				$this->_site($result);
				$output[str_replace('ep_', '', $dbname)] = $result;
			} catch (Exception $e) {
				logger::error(print_r($e, true));
				echo $e->getMessage();
			}
		} else {
			// 所有站点
			$ts = time() - 86400 * 7;
			for ($i = $start_ep_id; $i <= $last_ep_id; $i++) {
				if (in_array($i, array('10002'))) {
					//continue;
				}
				if ($i > 36708) {
					$cfg['host'] = '10.66.141.207';
					$cfg['pw'] = '88d8K88rMhQse4MD';
					$tablepre = $cfg['tablepre'];
					$this->_db = &db::init($cfg);
				}
				try {
					$this->_db->query('USE ep_' . $i);
					$output[$i] = array();
					$this->_site($output[$i]);
					if (0 >= $output[$i]['count_total']) {
						unset($ep_list[$i]);
						unset($output[$i]);
					}
				} catch (Exception $e) {
					if (stripos($e->getMessage(), 'Unknown database') === false) {
						echo $i.'==='.$e->getMessage()."\n";
						logger::error($i."==".$e->getMessage());
					}
					continue;
				}
			}
		}

		$put_data = array();
		$put = array();
		$put = array(
			'负责人', '企业ID', '是否启用', '域名', '开通日期',
			'企业名称', '所属行业', '联系人', '联系人手机', '联系人邮箱',
			'管理员', '管理手机', '员工数', '应用开启数', '总数据量', '人均贡献量'
		);
		foreach ($plugin_list as $_p) {
			$put[] = $_p['cp_name'].'主数据量';
			$put[] = $_p['cp_name'].'总数据量';
		}
		$put_data[] = implode(',', $put);
		foreach ($ep_list as $_kkk => $_e) {
			if (!isset($output[$_e['ep_id']])) {
				continue;
			}
			if (!$this->__in($_e['ep_mobilephone']) && !$this->__in($_e['ep_adminmobile'])) {
				continue;
			}
			$r = $output[$_e['ep_id']];
			$put = array();
			$adminer = '';
			if ($_e['ca_id'] && $adminers[$_e['ca_id']]) $adminer = $adminers[$_e['ca_id']]['ca_realname'];
			$put = array($adminer,
				$_e['ep_id'], ($_e['ep_wxcorpid'] ? 1 : 0), $_e['ep_domain'], rgmdate($_e['ep_created'], 'Y-m-d'),
				$_e['ep_name'], $_e['ep_industry'],
				$_e['ep_contact'], $_e['ep_mobilephone'], $_e['ep_email'],
				$_e['ep_adminrealname'], $_e['ep_adminmobile'],
				$r['membercount'], $r['plugincount'],
				$r['count_total'], $r['count_average']
			);
			foreach ($plugin_list as $_p) {
				if (!isset($r[$_p['cp_identifier']])) {
					$put[] = 0;
					$put[] = 0;
				} else {
					$put[] = $r[$_p['cp_identifier']][0];
					$put[] = $r[$_p['cp_identifier']][1];
				}
			}
			unset($_p);
			$put_data_list[$_kkk] = implode(',', $put);
			$orderby_total[$_kkk] = $r['count_total'];
			$orderby_member[$_kkk] = $r['membercount'];
			$orderby_average[$_kkk] = $r['count_average'];
		}
		unset($output, $put, $_e, $plugin_list);

		if (!empty($this->__opts['limit'])) {
			// 取出比较活跃的企业
			array_multisort(
				$orderby_total, SORT_DESC,
				$orderby_member, SORT_DESC,
				$orderby_average, SORT_DESC,
				$put_data_list
			);
			unset($orderby_total, $orderby_member, $orderby_average);

			$put_data_list = array_slice($put_data_list, 0, $this->__opts['limit']);
		}

		$output = implode("\r\n", $put_data);
		$output .= "\r\n";
		$output .= implode("\r\n", $put_data_list);
		//$output = iconv('UTF8', 'GBK//IGNORE', $output);
		$ymd = rgmdate(startup_env::get('timestamp'), 'Ymd');
		if (0 < $this->_s_ts) {
			$ymd = rgmdate($this->_s_ts, 'Ymd');
		}
		file_put_contents(APP_PATH.'/data/yztjsite_'.$ymd.'.csv', $output);

		return;
	}

	/**
	 * 统计单个企业
	 */
	protected function _site(&$result) {

		$result['is_open'] = 0;

		// 应用开通过的数量
		$result['plugincount'] = 0;
		$result['is_open'] = 1;
		$result['membercount'] = $this->_db->result_first("SELECT COUNT(`m_uid`) FROM oa_member WHERE `m_status`<".voa_d_oa_member::STATUS_REMOVE);

		// 读取所有开通过的插件
		$opened = false;
		$q = $this->_db->query('SELECT * FROM oa_common_plugin WHERE cp_available>0 AND cp_available<255');
		$plugin_names = array();
		// 总数据量
		$count_total = 0;
		while ($row = $this->_db->fetch_array($q)) {

			// 开通过的应用数
			$result['plugincount']++;

			// 方法是否存在
			$func = '_ct_'.$row['cp_identifier'];
			if (!method_exists($this, $func)) {
				continue;
			}

			$count = 0;
			$count_do = 0;
			$this->$func($count, $count_do);
			$result[$row['cp_identifier']] = array($count, $count_do);
			$count_total = $count_total + $count_do;
		}

		$result['count_total'] = $count_total;
		$result['count_average'] = $result['membercount'] > 0 ? round($count_total/$result['membercount'], 2) : -1;

		return true;
	}

	/**
	 * 统计邀请数据
	 * @param int $count 操作人数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_invite(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;

		// 发起数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_invite_personnel WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		$count = $this->_db->result($q);

		$count_do = $count;

		return true;
	}

	/**
	 * 统计聊天数据
	 * @param int $count 操作人数
	 * @param int $count_do 操作人次
	 * @return boolean
	 */
	protected function _ct_chatgroup(&$count, &$count_do) {

		$count = (int)$count;
		$count_do = (int)$count_do;
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_chatgroup_record WHERE cgr_created>{$this->_s_ts} AND cgr_created<{$this->_e_ts} AND cgr_status < 3");
		$count = $this->_db->result($q);
		$count_do = $count;

		return true;
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport WHERE dr_created>{$this->_s_ts} AND dr_created<{$this->_e_ts} AND dr_status < 3");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_dailyreport_post WHERE drp_created>{$this->_s_ts} AND drp_created<{$this->_e_ts} AND drp_status<3");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_record WHERE sr_created>{$this->_s_ts} AND sr_created<{$this->_e_ts} AND sr_status < 3");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_location WHERE sl_created>{$this->_s_ts} AND sl_created<{$this->_e_ts} AND sl_status < 3");
		$count = $this->_db->result($q) + $count;

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_sign_detail WHERE sd_created>{$this->_s_ts} AND sd_created<{$this->_e_ts} AND sd_status < 3");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor WHERE af_created>{$this->_s_ts} AND af_created<{$this->_e_ts} AND af_status < 3");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_reply WHERE afr_created>{$this->_s_ts} AND afr_created<{$this->_e_ts} AND afr_status < 3");
		$count_do = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_comment WHERE afc_created>{$this->_s_ts} AND afc_created<{$this->_e_ts} AND afc_status < 3");
		$count_do = $this->_db->result($q) + $count_do;

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askfor_proc WHERE afp_created>{$this->_s_ts} AND afp_created<{$this->_e_ts} AND afp_condition>1 AND afp_status < 3");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff WHERE ao_created>{$this->_s_ts} AND ao_created<{$this->_e_ts} AND ao_status < 3");
		$count = $this->_db->result($q);
		//待升级
		/*
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_post WHERE aopt_created>{$this->_s_ts} AND aopt_created<{$this->_e_ts}");
		$count_do = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_askoff_proc WHERE aopc_created>{$this->_s_ts} AND aopc_created<{$this->_e_ts} AND aopc_status>1");
		$count_do = $this->_db->result($q) + $count_do;*/

		// 操作数
		$count_do = $count;

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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse WHERE rb_created>{$this->_s_ts} AND rb_created<{$this->_e_ts} AND rb_status < 5");
		$count = $this->_db->result($q);

		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_bill WHERE rbb_created>{$this->_s_ts} AND rbb_created<{$this->_e_ts} AND rbb_status < 3");;
		$count = $this->_db->result($q) + $count;

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_reimburse_proc WHERE rbpc_created>{$this->_s_ts} AND rbpc_created<{$this->_e_ts} AND rbpc_status>1 AND rbpc_status < 6");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting WHERE mt_created>{$this->_s_ts} AND mt_created<{$this->_e_ts} AND mt_status < 3");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_meeting_mem WHERE mm_created>{$this->_s_ts} AND mm_created<{$this->_e_ts} AND mm_status>1 AND mm_status < 5");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_vnote WHERE vn_created>{$this->_s_ts} AND vn_created<{$this->_e_ts} AND vn_status < 3");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_vnote_mem WHERE vnm_created>{$this->_s_ts} AND vnm_created<{$this->_e_ts} AND vnm_status < 4");
		$count_do = $this->_db->result($q) + $count;

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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes WHERE mi_created>{$this->_s_ts} AND mi_created<{$this->_e_ts} AND mi_status < 3");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_minutes_post WHERE mip_created>{$this->_s_ts} AND mip_created<{$this->_e_ts} AND mip_status < 3");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_project WHERE p_created>{$this->_s_ts} AND p_created<{$this->_e_ts} AND p_status < 5");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_project_proc WHERE pp_created>{$this->_s_ts} AND pp_created<{$this->_e_ts} AND pp_status>1 AND pp_status < 3");
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
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect WHERE ins_created>{$this->_s_ts} AND ins_created<{$this->_e_ts} AND ins_status < 3");
		$count = $this->_db->result($q);

		// 操作数
		$q = $this->_db->query("SELECT COUNT(*) FROM oa_inspect_score WHERE isr_created>{$this->_s_ts} AND isr_created<{$this->_e_ts} AND isr_status < 3");
		$count_do = $this->_db->result($q) + $count;

		return true;
	}

	/**
	 * 统计营销CRM
	 * @param number $count 备注数
	 * @param number $count_do 分享数
	 * @return boolean
	 */
	/*protected function _ct_travel(&$count = 0, &$count_do = 0) {

		$count = (int)$count;
		$count_do = (int)$count_do;

		// 备注数
		$q = $this->_db->query("SELECT COUNT(`crk_id`) FROM `oa_travel_customer_remark` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts}");
		$count = $this->_db->result($q);

		// 分享数
		$q = $this->_db->query("SELECT COUNT(`tsc_id`) FROM `oa_travel_share_count` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts}");
		$count_do = $this->_db->result($q);

		return true;
	}*/

	/**
	 * 统计新闻公告
	 * @param number $count 新闻发表数
	 * @param number $count_do 总的阅读数
	 */
	protected function _ct_news(&$count = 0, &$count_do = 0) {

		// 新闻发表数
		$count = (int)$this->_db->result_first("SELECT COUNT(`ne_id`) FROM `oa_news` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");

		// 总阅读数
		$count_do = $count + (int)$this->_db->result_first("SELECT COUNT(`nre_id`) FROM `oa_news_read` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status <3");

		$q = $this->_db->query("SELECT COUNT(*) FROM `oa_news_like` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		$count_do = $count_do + $this->_db->result($q);
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
		$count = (int)$this->_db->result_first("SELECT COUNT(`tid`) FROM `oa_thread` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		// 评论 + 评论回复数
		$count_do = $count + (int)$this->_db->result_first("SELECT COUNT(`pid`) FROM `oa_thread_post` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		$count_do = $count_do + (int)$this->_db->result_first("SELECT COUNT(`prid`) FROM `oa_thread_post_reply` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`m_uid`) FROM `oa_member` WHERE m_created>{$this->_s_ts} AND m_created<{$this->_e_ts} AND m_status < 3");
		// 通讯录近期更新人数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`m_uid`) FROM `oa_member` WHERE m_updated>{$this->_s_ts} AND m_updated<{$this->_e_ts} AND m_status < 3");

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`woid`) FROM `oa_workorder` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		// 工单操作数
		$count_do = $count + (int)$this->_db->result_first("SELECT COUNT(`wologid`) FROM `oa_workorder_log` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`ta_id`) FROM `oa_train_article` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		$count_do = $count;

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`ta_id`) FROM `oa_showroom_article` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		$count_do = $count;

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`id`) FROM `oa_nvote` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		// 投票次数
		$count_do = $count + (int)$this->_db->result_first("SELECT COUNT(`id`) FROM `oa_nvote_mem` WHERE updated>{$this->_s_ts} AND updated<{$this->_e_ts} AND status < 3");

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`acid`) FROM `oa_activity` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		// 报名次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`apid`) FROM `oa_activity_partake` WHERE updated>{$this->_s_ts} AND updated<{$this->_e_ts} AND status < 3");

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
		$count = (int)$this->_db->result_first("SELECT COUNT(`eid`) FROM `oa_express` WHERE created>{$this->_s_ts} AND created<{$this->_e_ts} AND status < 3");
		// 快递响应次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(`mid`) FROM `oa_express_mem` WHERE updated>{$this->_s_ts} AND updated<{$this->_e_ts} AND status < 3");

		$count_do += $count;
		return true;
	}

	// 统计红包
	protected function _ct_blessingredpack(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 红包发起次数
		$count = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_blessing_redpack` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND status < 3");
		// 红包领取次数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_blessing_redpack_log` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND status < 3");

		$count_do += $count;
		return true;
	}

	// 培训
	protected function _ct_jobtrain(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;
		// 发起数
		$count = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_jobtrain_article` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");
		// 回复数
		$count_do = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_jobtrain_coll` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");
		$count_do = $count_do + (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_jobtrain_comment` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");
		$count_do = $count_do + (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_jobtrain_comment_zan` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");

		$count_do += $count;
		return true;
	}

	// 考试
	protected function _ct_exam(&$count = 0, &$count_do = 0) {

		$count = 0;
		$count_do = 0;

		$count = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_exam_paper` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");
		$count_do = (int)$this->_db->result_first("SELECT COUNT(*) FROM `oa_exam_ti_tj` WHERE `created`>{$this->_s_ts} AND `created`<{$this->_e_ts} AND `status` < 3");

		$count_do += $count;
		return true;
	}

	private function __in($mobile) {
		return true;
		return in_array($mobile, array());
	}

}
