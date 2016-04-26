<?php
/**
 * importmem.php
 * 比对数据表
 * @uses php tool.php -n importmem
 * $Author$
 * $Id$
 */
class voa_backend_tool_importmem extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	private $__db;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = db::init($cfg);
		$db->query("USE ep_30750");
		$this->__db = $db;

		startup_env::set('domain', 'metrochina');
		voa_h_conf::init_db();

		$wq_u = voa_wxqy_addressbook::instance();
		$wq_m = uda::factory('voa_uda_frontend_member_update');

		$errors = array();

		$file = file_get_contents(APP_PATH . '/src/include/backend/tool/mdl2.txt');
		$arr = explode("\n", str_replace("\r", "\n", $file));
		$i = 0;//var_dump($arr[1]);var_dump($arr[2]);
		$openids = array();
		foreach ($arr as $_k => $_meminfo) {
			if (0 == $_k) {
				continue;
			}

			$mem = array();
			$this->_genrate_meminfo($mem, $_meminfo);

			$openids[$mem['m_openid']] ++;
			// 判断用户是否存在
			//echo $_meminfo."SELECT * FROM oa_member WHERE m_openid='{$mem['m_openid']}'";
			$query = $db->query("SELECT * FROM oa_member WHERE m_openid='{$mem['m_openid']}'");
			if ($row = $db->fetch_array($query)) {
				continue;
			}

			//$errors[] = $_meminfo;
			$result = array();
			if (!$wq_m->update($mem, $result)) {
				$errors[] = $_meminfo . ';' . $wq_m->errcode . ':' . $wq_m->errmsg;
				echo $_meminfo . ';' . $wq_m->errcode . ':' . $wq_m->errmsg."\n";
			}
			//print_r($mem);exit("ok");
		}

		file_put_contents(APP_PATH . '/data/errors.csv', implode("\n", $errors), FILE_APPEND);
		foreach ($openids as $_openid => $_ct) {
			if (1 < $_ct) echo $_openid . ',';
		}
	}

	// 解析用户信息
	protected function _genrate_meminfo(&$minfo, $data) {

		$mem = explode(';', $data);//var_dump($data);var_dump($mem);
		// 用户信息
		$minfo = array();
		if (empty($mem[0])) {
			echo $data . ';err用户名错误';exit;
			return false;
		}

		$minfo['m_username'] = $mem[0];
		if (!empty($mem[1])) {
			$minfo['m_openid'] = $mem[1];
		}

		$g2num = array('男' => 1, '女' => 2);
		$minfo['m_gender'] = isset($g2num[$mem[2]]) ? $g2num[$mem[2]] : 0;

		$cdid = 0;
		$qywxid = 0;
		$this->_up_dp($mem[3], $cdid, $qywxid);
		$minfo['cd_id'] = array($cdid);

		if (!empty($mem[4])) {
			$minfo['cj_name'] = $mem[4];
		}

		if (!empty($mem[5])) {
			$minfo['m_weixinid'] = $mem[5];
		}

		if (!empty($mem[6])) {
			$minfo['m_mobilephone'] = $mem[6];
		}

		if (!empty($mem[7])) {
			$minfo['m_email'] = $mem[7];
		}

		if (!empty($mem[8])) {
			$minfo['mf_ext2'] = $mem[8];
		}

		if (!empty($mem[9])) {
			$minfo['mf_qq'] = $mem[9];
		}

		if (!empty($mem[10])) {
			$minfo['mf_address'] = $mem[10];
		}

		if (!empty($mem[11])) {
			$minfo['mf_idcard'] = $mem[11];
		}

		if (!empty($mem[12])) {
			$minfo['mf_telephone'] = $mem[12];
		}

		if (!empty($mem[13])) {
			$minfo['mf_birthday'] = $mem[13];
		}

		return true;
	}

	// 更新部门信息
	protected function _up_dp($dp, &$cdid, &$qywxid) {

		$dp = trim($dp);
		$cdid = 1;
		$qywxid = 1;
		if (empty($dp)) {
			return true;
		}

		$uda_dp = uda::factory('voa_uda_frontend_department_update');
		$dps = explode('/', $dp);
		foreach ($dps as $_k => $_v) {
			$_v = preg_replace('/\s+/s', '', $_v);
			$query = $this->__db->query("SELECT * FROM oa_common_department WHERE cd_upid='{$cdid}' AND cd_name='{$_v}' AND cd_status<3");
			if ($cd = $this->__db->fetch_array($query)) {
				$cdid = $cd['cd_id'];
				$qywxid = $cd['cd_qywxid'];
				continue;
			}/** else {
				echo $dp.'|'.$cdid.','.$_v;
				exit('error.');
			}*/

			$cd = array();
			$dpt = array(
				'cd_upid' =>$cdid,
				'cd_name' => $_v
			);
			if (!$uda_dp->update(array(), $dpt, $cd)) {
				echo $dp."|".$uda_dp->errcode.':'.$uda_dp->errmsg."({$_v})";exit;
			}
			//echo $dp." => ({$_v})";exit;
			$cdid = $cd['cd_id'];
		}

		return true;
	}
}
