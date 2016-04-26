<?php
/**
 * enterprise.php
 * 企业信息表数据访问操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_enterprise extends voa_uda_uc_base {

	/**
	 * 当前操作的企业信息
	 * @var array
	 */
	public $enterprise = array();

	public function __construct() {
		parent::__construct();

		$this->errcode = 1;
		$this->errmsg = 'uda enterprise error';
	}

	/**
	 * 开通一个企业OA账号
	 * @param array $data 企业信息、所选的web主机id、所选的数据库主机id
	 * @param array $return_data <strong style="color:red">(引用结果)</strong>返回的数据
	 * @param number $step 操作步骤
	 * @return boolean
	 */
	public function open($data, &$return_data, $is_cp = false) {

		/**
		 * 1.检查提交的企业数据合法性（主要检测是否有重复）
		 * 2.分配web主机，原则：首先考虑是否指定了（比如总后台开通、代理开通）主机，
		 *                    未指定则先找到未满载但负载最高的，找不到则分配一个负载最低的
		 * 3.分配db主机，分配原则同上
		 * 4.写入企业信息
		 *
		 * 6.rpc写入企业站数据
		 * 6.1 建库
		 * 6.2 建表
		 * 6.3 默认数据
		 * 6.4 站点setting信息（企业ID、域名等）
		 * 6.5 开启默认应用（迭代会按企业类型开启不同的默认应用）
		 * 6.6 写入管理员信息
		 * 6.7 写入管理员对应的通讯录、员工信息
		 * 6.8 返回管理员员工m_uid
		 * 7 uc写入员工、企业对应关系
		 * 8 返回企业信息
		 *
		 * 5.api写入dns
		 */

		/*
		$uda_uc_member = &uda::factory('voa_uda_uc_member');
		$uc_member_data = array();
		if (isset($data['m_id'])) {
			// 已登录的用户创建企业号
			if (!$uda_uc_member->get_by_id($data['m_id'], $uc_member_data)) {
				$this->errcode = $uda_uc_member->errcode;
				$this->errmsg = $uda_uc_member->errmsg;
				return false;
			}

			if ($this->s('voa_s_uc_enterprise')->count_by_m_id($uc_member_data['m_id']) > 0) {
				// 检查该uc用户是否已经开通了一个企业号
				return $this->set_errmsg(voa_errcode_uc_register::UC_MEMBER_HAVE_ENTERPRISE, 1);
			}
			$data['mobilephone'] = $uc_member_data['m_mobilephone'];
			$data['realname'] = $uc_member_data['m_realname'];
			$data['email'] = $uc_member_data['m_email'];
		}
		*/

		// 检查提交的数据
		if (!$this->validator_enterprise_base($data, $data)) {
			return false;
		}

		// 写入uc用户表数据
		/*
		if (empty($uc_member_data)) {
			$member_data = array(
				'mobilephone' => $data['mobilephone'],
				'realname' => $data['realname'],
				'email' => $data['email'],
				'password' => $data['password']
			);
			$uc_member_data = array();
			if (!$uda_uc_member->new_member($member_data, $uc_member_data)) {
				$this->errcode = $uda_uc_member->errcode;
				$this->errmsg = $uda_uc_member->errmsg;
				return false;
			}
		}
		*/

		// 分配web主机
		$enterprise_web_host = array();
		if (!$this->assign_webhost($data, $enterprise_web_host)) {
			return false;
		}

		// 分配DB主机
		$enterprise_db_host = array();
		if (!$this->assign_dbhost($data, $enterprise_db_host)) {
			return false;
		}
		$enterprise_db_host_admin = $enterprise_db_host;

		// 用于授权给DB的服务器
		$db_ip = $enterprise_web_host['web_ip'];
		if ($enterprise_web_host['web_lanip'] && $enterprise_db_host['db_lanip']) {
			$db_ip = $enterprise_web_host['web_lanip'];
		}

		// 企业号
		$enumber = $data['enumber'];
		// 域名(完整)
		$domain = $data['enumber'].'.'.config::get('voa.oa_top_domain');

		// 是否启用了微信企业号
		$ep_wxqy = !empty($data['ep_wxqy']) ? 1 : 0;

		// 写入UC数据信息
		try {
			$this->s('voa_s_uc_enterprise')->begin();

			// 写入企业基本信息
			$ep_id = $this->s('voa_s_uc_enterprise')->insert(array(
				'ep_wxqy' => voa_d_uc_enterprise::WXQY_CLOSE,
				'ep_enumber' => $data['enumber'],
				'ep_domain' => $domain,// 完整域名
				'ep_name' => $data['ename'],
				//'m_id' => $uc_member_data['m_id'], // 企业主在uc的用户id
				'ep_adminemail' => $data['email'],
				'ep_adminmobilephone' => $data['mobilephone'],
				'ep_adminrealname' => $data['realname'],
				'ep_adminunionid' => $data['unionid'],
				'ep_status' => voa_d_uc_enterprise::STATUS_DB,
			), true);

			// 数据库用户名
			$enterprise_db_host['db_user'] = 'ep_'.$ep_id;
			// 数据库名(同数据库用户名)
			$enterprise_db_host['db_name'] = $enterprise_db_host['db_user'];
			// 数据库密码
			$enterprise_db_host['db_pw'] = $ep_id.'@vChangYi';

			// 写入企业扩展信息
			$this->s('voa_s_uc_enterprise_profile')->insert(array(
				'ep_id' => $ep_id,
				'db_id' => $enterprise_db_host['db_id'],
				'epp_dbhost' => $enterprise_db_host['db_host'],
				'epp_dbuser' => $enterprise_db_host['db_user'],
				'epp_industry' => $data['industry'],
				'epp_companysize' => $data['companysize'],
				'epp_ref' => $data['ref'],
				'epp_ref_domain' => $data['ref_domain'],
				'epp_dbpw' => $enterprise_db_host['db_pw'],
				'epp_dbname' => $enterprise_db_host['db_name'],
				'web_id' => $enterprise_web_host['web_id'],
				'epp_webip' => $enterprise_web_host['web_ip'],
			));

			// 为DB主机池增加一台负载计数
			$this->s('voa_s_uc_dbhost')->count_add_by_db_id($enterprise_db_host['db_id']);

			// 为web主机池增加一台负载计数
			$this->s('voa_s_uc_webhost')->count_add_by_web_id($enterprise_web_host['web_id']);

			/** 呼叫 RPC 写入企业站数据 */

			// 构造企业OA站的api url
			$scheme = config::get('voa.oa_http_scheme');
			$rpc_url = $scheme.$domain.'/api.php';
			$rpc_url = config::get(startup_env::get('app_name').'.oa_http_scheme').$domain.'/api.php';
			// 调用OA
			$rpc_oa = new voa_client_oa(config::get(startup_env::get('app_name').'.rpc.client.auth_key'));
			$method = 'site.open';
			$args = array(
				'enterprise' => array(
					'domain' => $domain,
					'name' => $data['ename'],
					'ep_id' => $ep_id,
					'ep_wxqy' => $ep_wxqy,
				),
				'adminer' => array(
					'mobilephone' => $data['mobilephone'],
					'realname' => $data['realname'],
					'email' => $data['email'],
					'password' => $data['password'],
				),
				'dbhost' => array(
					'dbhost' => $enterprise_db_host['db_host'],
					'dbname' => $enterprise_db_host['db_name'],
					'dbuser' => $enterprise_db_host['db_user'],
					'dbpw' => $enterprise_db_host['db_pw'],
					'lanip' => $db_ip,
				),
				'dbadmin' => array(
					'host' => $enterprise_db_host_admin['db_host'],
					'user' => $enterprise_db_host_admin['db_user'],
					'pw' => $enterprise_db_host_admin['db_pw'],
				),
			);
			// 呼叫企业站RPC请求
			$result = $rpc_oa->call($rpc_url, $method, $args, $enterprise_web_host['web_ip']);
			$this->errmsg($rpc_oa->errno, $rpc_oa->errmsg);
			if ($rpc_oa->errno) {
				$this->errcode = $rpc_oa->errno;
				$this->errmsg = $rpc_oa->errmsg;
				return false;
			} else {
				$this->result = $result;

				// 写入用户与企业对应关系表
				/*
				$this->s('voa_s_uc_member2enterprise')->update_by_ep_id_m_uid($ep_id, $result['m_uid'], array(
					'mobilephone' => $data['mobilephone'],
					'email' => $data['email'],
					'unionid' => $data['unionid']
				));
				*/
				$this->s('voa_s_uc_enterprise')->update(array('m_uid' => $result['m_uid']), $ep_id);
			}

			// 将企业信息写入主站数据库
			$enterprise_data = array();
			$this->enterprise($ep_id, $enterprise_data);
			$this->vchangyi_enterprise_api($ep_id, $enterprise_data);

			$this->s('voa_s_uc_enterprise')->commit();
		} catch (Exception $e) {
			$this->s('voa_s_uc_enterprise')->rollback();
			logger::error(print_r($e, true));
			$this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_DB_ERROR);
			return false;
		}

		if (empty($ep_id)) {
			$this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_EP_ID_NONE);
		}

		// 返回结果集
		$return_data = array(
			'ep_id' => $ep_id,// 企业站ID
			'submit' => $data,// 经过格式化后提交的企业信息数组
			'oasite' => $this->result,// 来自企业站rpc返回的数据
		);

		return true;
	}

	/**
	 * 校验企业(开通时)的基本信息
	 * @param array $data
	 * @param array $reset_data <strong style="color:red">(引用结果)</strong>格式化后的信息
	 * @return boolean
	 */
	public function validator_enterprise_base($data, &$reset_data = array()) {

		$reset_data = $data;

		// 初步检查变量是否提交
		if (empty($data['mobilephone'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_MOBILE_EMPTY);
		}
		$reset_data['mobilephone'] = trim($data['mobilephone']);

		if (empty($data['realname'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_REALNAME_EMPTY);
		}
		$reset_data['realname'] = trim($data['realname']);

		if (empty($data['email'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_EMAIL_EMPTY);
		}
		$reset_data['email'] = trim($data['email']);

		if (empty($data['ename'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENAME_EMPTY);
		}
		$reset_data['ename'] = trim($data['ename']);

		if (empty($data['enumber'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_EMPTY);
		}
		$reset_data['enumber'] = rstrtolower(trim($data['enumber']));

		if (empty($data['password'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_PASSWORD_EMPTY);
		}

		// 检查真实姓名
		if ($reset_data['realname'] != rhtmlspecialchars($reset_data['realname'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_REALNAME_FORMAT);
		}
		if (!validator::is_realname($reset_data['realname'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_REALNAME_LENGTH);
		}

		// 检查企业名称
		if ($reset_data['ename'] != rhtmlspecialchars($reset_data['ename'])) {
			return $this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_NAME_FORMAT);
		}
		if (!validator::is_string_count_in_range($reset_data['ename'], 1, 50)) {
			return $this->error_msg(voa_errcode_uc_system::UC_OPEN_ENTERPRISE_NAME_LENGTH);
		}

		// 进一步检查手机号、企业号以及邮箱地址
		if (!$this->check_enterprise_mobilephone($reset_data['mobilephone'])) {
			return false;
		}
		if (!$this->check_enterprise_enumber($reset_data['enumber'])) {
			return false;
		}
		if (!$this->check_enterprise_email($reset_data['email'])) {
			return false;
		}

		// 如果提供了微信唯一unionid则进行验证
		if (isset($data['unionid']) && !$this->check_enterprise_unionid($data['unionid'])) {
			return false;
		}

		return true;
	}

	/**
	 * 分配一个 web 主机
	 * @param array $data
	 * @param array $host_info
	 * @return boolean
	 */
	public function assign_webhost($data, &$host_info) {

		// 确定是否已经指定了一台web主机id
		$web_id = isset($data['web_id']) && is_numeric($data['web_id']) ? $data['web_id'] : 0;

		// 分配给当前企业站的web主机信息
		$host_info = array();
		if ($web_id && !($host_info = $this->s('voa_s_uc_webhost')->fetch($web_id))) {
			// 如果指定了一台web主机id，则检查该主机是否存在
			return $this->error_msg(voa_errcode_uc_system::UC_WEB_HOST_NOT_EXISTS, $web_id);
		}

		/**
		 * 自动分配 WEB 主机
		 * 原则：首先分配未达到最大负荷且负载最高的一台，如果未找到匹配的则分配一台负载最低的
		 */
		if (empty($host_info)) {

			// 找到未满载但负载最高的一台
			$host_info = $this->s('voa_s_uc_webhost')->fetch_by_maximum();

			if (empty($host_info)) {
				// 找到负载最低的一台
				$host_info = $this->s('voa_s_uc_webhost')->fetch_by_minimum();
			}

			if (empty($host_info)) {
				// 仍旧找不到则返回错误
				return $this->error_msg(voa_errcode_uc_system::UC_WEB_HOST_EMPTY);
			}
		}

		return true;
	}

	/**
	 * 分配一台数据库DB主机
	 * @param array $data
	 * @param array $host_info
	 * @return boolean
	 */
	public function assign_dbhost($data, &$host_info) {

		// 确定是否指定了一台DB主机
		$db_id = isset($data['db_id']) && is_numeric($data['db_id']) ? $data['db_id'] : 0;

		// 分配给当前企业的DB主机信息
		$host_info = array();
		if ($db_id && !($host_info = $this->s('voa_s_uc_dbhost')->fetch($db_id))) {
			// 指定了具体的主机，检查该主机是否存在
			return $this->error_msg(voa_errcode_uc_system::UC_DB_HOST_NOT_EXISTS, $db_id);
		}

		/**
		 * 自动分配一台 DB 主机
		 * 原则：首先分配未达到最大负荷且负载最高的一台，如果未找到匹配的则分配一台负载最低的
		 */
		if (empty($host_info)) {

			// 找到未满载但负载最高的一台
			$host_info = $this->s('voa_s_uc_dbhost')->fetch_by_maximum();

			if (empty($host_info)) {
				// 找到负载最低的一台
				$host_info = $this->s('voa_s_uc_dbhost')->fetch_by_minimum();
			}

			if (empty($host_info)) {
				// 仍旧找不到则返回错误
				return $this->error_msg(voa_errcode_uc_system::UC_DB_HOST_EMPTY);
			}
		}

		return true;
	}

	/**
	 * 检查企业注册人手机号（不在ep_id内）
	 * @param string $mobilephone
	 * @param number $ep_id 如果小于0则不检查是否注册过
	 * @return boolean
	 */
	public function check_enterprise_mobilephone($mobilephone, $ep_id = 0) {
		if (!$mobilephone || !validator::is_mobile($mobilephone)) {
			return $this->error_msg(voa_errcode_uc_system::UC_MOBILE_ERROR, $mobilephone);
		}
		if ($this->s('voa_s_uc_enterprise')->count_by_field_not_in_ep_id('ep_adminmobilephone', $mobilephone, $ep_id) > 0) {
			return $this->error_msg(voa_errcode_uc_system::UC_MOBILE_EXISTS, $mobilephone);
		}

		return true;
	}

	/**
	 * 检查企业注册人Email（不在ep_id内）
	 * @param string $email
	 * @param number $ep_id
	 * @return boolean
	 */
	public function check_enterprise_email($email, $ep_id = 0) {
		if (!$email || !validator::is_email($email)) {
			return $this->error_msg(voa_errcode_uc_system::UC_EMAIL_ERROR);
		}
		if ($this->s('voa_s_uc_enterprise')->count_by_field_not_in_ep_id('ep_adminemail', $email, $ep_id) > 0) {
			return $this->error_msg(voa_errcode_uc_system::UC_EMAIL_EXISTS);
		}

		return true;
	}

	/**
	 * 检查企业号是否重复（不在ep_id内）
	 * @param string $enumber
	 * @param number $ep_id
	 * @return boolean
	 */
	public function check_enterprise_enumber($enumber, $ep_id = 0) {
		if (!$enumber || !preg_match('/^[a-z]([a-z0-9]{4,19})$/i', $enumber)) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_ERROR, ' - 应该以字母开头允许字母与数字混合且长度5-20字');
		}
		if ($this->s('voa_s_uc_enterprise')->count_by_field_not_in_ep_id('ep_enumber', $enumber, $ep_id) > 0) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_EXISTS);
		}

		// 禁止注册的企业号（二级域名），多个之间使用半角逗号分隔
		$black_list = "dcapi,admin,testing,changyi,vchangyi,redmine";
		if (in_array(rstrtolower($enumber), explode(',', $black_list))) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_BLACKLIST);
		}
		return true;
	}

	/**
	 * 检查企业注册人微信unionid（不在ep_id内）
	 * @param string $unionid
	 * @param number $ep_id
	 * @return boolean
	 */
	public function check_enterprise_unionid($unionid, $ep_id = 0) {
		if ($unionid && $this->s('voa_s_uc_enterprise')->count_by_field_not_in_ep_id('ep_adminunionid', $unionid, $ep_id) > 0) {
			return $this->error_msg(voa_errcode_uc_system::UC_WXUNIONID_EXISTS);
		}

		return true;
	}

	/**
	 * 获取指定企业ID的信息
	 * @param number $ep_id
	 * @param array $enterprise <strong style="color:red">(引用结果)</strong>企业信息
	 * @return boolean
	 */
	public function enterprise(&$ep_id, &$enterprise) {
		$ep_id = (int)$ep_id;
		if ($ep_id <= 0) {
			return false;
		}
		if (empty($this->enterprise[$ep_id])) {
			$tmp = $this->s('voa_s_uc_enterprise_profile')->fetch($ep_id);
			$this->enterprise[$ep_id] = array_merge($tmp, $this->s('voa_s_uc_enterprise')->fetch($ep_id));
			unset($tmp);
		}

		$enterprise = $this->enterprise[$ep_id];

		return true;
	}

	/**
	 * 企业号检查
	 * @param string $enumber
	 * @param array $enterprise <strong style="color:red">(引用结果)</strong>企业基本信息
	 * @return boolean
	 */
	public function check_enumber($enumber, &$enterprise = array()) {

		if (!$enumber || !preg_match('/^[a-z]([a-z0-9]{1,19})$/i', $enumber)) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_CHECK_ERROR, '');
		}

		$serv_enterprise = &service::factory('voa_s_uc_enterprise');
		$enterprise = $serv_enterprise->fetch_by_enumber($enumber);
		if (empty($enterprise)) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_CHECK_NOT_EXISTS);
		}

		return true;
	}

	/**
	 * 通过域名（公司号）获取企业信息
	 * @param string $enumber 域名
	 * @param array $profile (引用结果)企业信息
	 * @return boolean
	 */
	public function get_by_enumber($enumber, array &$profile) {

		$serv_enterprise = &service::factory('voa_s_uc_enterprise');
		$profile = $serv_enterprise->fetch_by_enumber($enumber);
		if (empty($profile)) {
			return $this->error_msg(voa_errcode_uc_system::UC_ENUMBER_CHECK_NOT_EXISTS);
		}

		return true;
	}

}
