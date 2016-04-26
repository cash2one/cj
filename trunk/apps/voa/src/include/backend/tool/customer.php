<?php
/**
 * customer.php
 * 统计某个应用使用的客户列表
 * @uses php tool.php -n customer -day 30 -count 10 -i news
 * -day 数据天数（暂时无效）
 * -count 获取活跃度比较高的前count个客户列表
 * -i 应用的唯一标识名
 * $Author$
 * $Id$
 */
class voa_backend_tool_customer extends voa_backend_base {
	/** 配置参数 */
	private $__opts = array();
	/** 数据库连接 */
	protected $__db;
	/** 时间戳 */
	private $__ts = 0;
	/** 需要统计的天数 */
	private $__days = 30;
	/** 待输出的客户总数 */
	private $__count = 10;
	/** 待统计的应用唯一标识符 */
	private $__identifier = '';

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		// 连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->__db = db::init($cfg);

		// 统计天数范围
		if (!empty($this->__opts['day'])) {
			$this->__days = (int)$this->__opts['day'];
		}
		// 列出结果数
		if (!empty($this->__opts['count'])) {
			$this->__count = (int)$this->__opts['count'];
		}
		// 应用的唯一标识符
		if (!empty($this->__opts['i'])) {
			$this->__identifier = (string)$this->__opts['i'];
		}

		// 唯一标识符不能为空
		if (empty($this->__identifier)) {
			$this->__output('option \'-i\' value invalid');
		}
		// 待统计数据的最小日期
		$this->__ts = time() - $this->__days * 86400;

		// 确定总后台数据库名
		$db_cyadmin = '';
		foreach (config::get('voa.db.cyadmin') as $_db) {
			if (isset($_db['dbname'])) {
				$db_cyadmin = $_db['dbname'];
				break;
			}
		}
		if (empty($db_cyadmin)) {
			$this->__output('config.voa.db.cyadmin dbname error');
		}
		$this->__db->query("USE ".$db_cyadmin);
		// 获取最后注册的数据库ID
		$q = $this->__db->query("SELECT `ep_id` FROM `cy_enterprise_profile` ORDER BY `ep_id` DESC LIMIT 1");
		$last_ep_id = $this->__db->result($q);
		// 站点数
		$site_count = $last_ep_id - 10002;

		$ignore_epid = array('10002');
		$ignore_domain = array('demo.vchangyi.com', 'changyineibu.vchangyi.com');

		$iii = 0;
		// 站点数据列表
		$total_list = array();
		for ($i = 10002; $i < $last_ep_id; ++ $i) {

			// 库id被忽略
			if ($ignore_epid && in_array($i, $ignore_epid)) {
				continue;
			}

			//$iii++;
			//if ($iii > 1000) {
				//break;
			//}
			try {
				$this->__db->query('USE ep_'.$i);

				// 站点配置
				$_settings = array();
				$qq = $this->__db->query("SELECT * FROM `oa_common_setting`");
				while ($_a = $this->__db->fetch_array($qq)) {
					$_settings[$_a['cs_key']] = $_a['cs_type'] == voa_d_oa_common_setting::TYPE_ARRAY ? unserialize($_a['cs_value']) : $_a['cs_value'];
				}
				// 域名被忽略
				if ($ignore_domain && in_array(strtolower($_settings['domain']), $ignore_domain)) {
					continue;
				}

				// 当前站点的用户数
				$_member_count = (int)$this->__db->result_first("SELECT COUNT(`m_uid`) FROM `oa_member` WHERE
						`m_status`<".voa_d_oa_member::STATUS_REMOVE);
				if (empty($_member_count)) {
					continue;
				}
				// 应用对应的数据表名列表
				$_tables = array();
				$q = $this->__db->query("SHOW TABLES LIKE 'oa_{$this->__identifier}%'");
				while ($row = $this->__db->fetch_array($q)) {
					$_tables[] = reset($row);
				}

				//判断是否开启（或曾经开启）了该应用
				if (empty($_tables)) {
					continue;
				}

				// 数据量总数
				$_data_count = 0;
				// 最后更新时间
				$_lasttime = 0;
				foreach ($_tables as $_t) {
					$_data_count = $_data_count + $this->__db->result_first("SELECT COUNT(*) FROM `{$_t}`");
					$_created_field = $this->__db->result_first("SHOW COLUMNS FROM `{$_t}` where Field like '%created'");
					$__lasttime = (int)$this->__db->result_first("SELECT `{$_created_field}` FROM `{$_t}` ORDER BY `{$_created_field}` DESC LIMIT 1");
					if ($_lasttime < $__lasttime) {
						$_lasttime = $__lasttime;
					}
				}
				if ($_data_count <= 0 || $_lasttime <= 0) {
					continue;
				}

				$total_list[$i] = array(
					'ep_id' => $i,
					'member_count' => $_member_count,
					'data_count' => $_data_count,
					'average_count' => round($_data_count/$_member_count, 3),
					'lasttime' => date('Y-m-d H:i:s', $_lasttime)
				);
				$rank_member_count[$i] = $_member_count;
				$rank_data_count[$i] = $_data_count;
				$rank_average_count[$i] = round($_data_count/$_member_count, 3);
				$rank_lasttime[$i] = $_lasttime;
				$rank_epid[$i] = $i;
			} catch (Exception $e) {
				continue;
			}
		}

		// 重新排序
		array_multisort($rank_data_count, SORT_DESC,
			$rank_member_count, SORT_DESC,
			$rank_average_count, SORT_DESC,
			$rank_lasttime, SORT_DESC,
			$rank_epid, SORT_ASC,
			$total_list);

		$list = array();
		$epids = array();
		foreach (array_slice($total_list, 0, $this->__count) as $_d) {
			$epids[] = $_d['ep_id'];
			$list[$_d['ep_id']] = $_d;
		}

		// 找到企业信息
		$this->__db->query("USE {$db_cyadmin}");
		$query = $this->__db->query("SELECT * FROM `cy_enterprise_profile` WHERE `ep_id` IN (".implode(',', $epids).")");
		// 企业信息
		$enterprises = array();
		while ($row = $this->__db->fetch_array($query)) {
			$row['ep_created'] = date('Y-m-d', $row['ep_created']);
			$enterprises[$row['ep_id']] = $row;
		}

		$title = array(
			'ep_id' => '企业ID',
			'ep_name' => '企业名称',
			'ep_created' => '开通时间',
			'ep_domain' => '二级域名',
			'ep_mobilephone' => '联系电话',
			'ep_email' => '邮箱地址',
			'member_count' => '员工数',
			'data_count' => '数据总量',
			'average_count' => '人均数据贡献量',
			'lasttime' => '数据最后更新时间'
		);

		$output = array();
		// 显示标题
		$_output = array();
		foreach ($title as $_k => $_v) {
			$_output[] = $_v;
		}
		$output[] = implode(",", $_output);
		unset($_k, $_v);
		// 显示数据
		foreach ($list as $_k => $_v) {
			$_output = array();
			foreach ($title as $_kk => $_vv) {
				if (stripos($_kk, 'ep_') === 0 && isset($enterprises[$_k]) && isset($enterprises[$_k][$_kk])) {
					$_output[] = $enterprises[$_k][$_kk];
				} elseif (isset($list[$_k]) && isset($list[$_k][$_kk])) {
					$_output[] = $list[$_k][$_kk];
				} else {
					$_output[] = '-'.$_kk.'='.$_k.'-';
				}
			}
			$output[] = implode(",", $_output);
		}

		file_put_contents(APP_PATH.'/data/customer_'.$this->__identifier.'_'.rgmdate(startup_env::get('timestamp'), 'Ymd').'.csv', implode("\n", $output));
	}

}
