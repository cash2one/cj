<?php
/**
 * voa_uda_frontend_member_base
 * 统一数据访问/用户表/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_base extends voa_uda_frontend_base {

	public $serv_member = null;
	public $serv_member_field = null;
	public $serv_member_department = null;
	public $serv_member_search = null;
	public $serv_member_share = null;
	public $serv_common_adminer = null;

    /** 全公司 */
    const PURVIEW_AllCOMPANY = 1;
    /** 仅本部门 */
    const PURVIEW_OLNYOWNSECTION = 2;
    /** 仅子部门 */
    const PURVIEW_OLNYCHILDSECTION = 3;

	/** 系统配置信息 */
	public $setting = null;

	/** 是否开通了企业微信 */
	public $use_qywx = null;

	/** 性别描述 */
	public $gender_list = array(
		voa_d_oa_member::GENDER_UNKNOWN => '未登记',
		voa_d_oa_member::GENDER_MALE => '男',
		voa_d_oa_member::GENDER_FEMALE => '女'
	);
    public  $purview_list=array(
        voa_d_oa_common_department::PURVIEW_AllCOMPANY=>'全公司',
        voa_d_oa_common_department::PURVIEW_OLNYOWNSECTION=>'仅本部门',
        voa_d_oa_common_department::PURVIEW_OLNYCHILDSECTION=>'仅子部门',

    );

	/** 在职状态描述 */
	public $active_list = array(
		voa_d_oa_member::ACTIVE_YES => '在职',
		voa_d_oa_member::ACTIVE_NO => '离职',
	);

	/** 必须要设置的用户字段 */
	public $member_key_required = array(
		'm_mobilephone' => '手机号码', 'm_email' => '邮箱',
		'm_username' => '真实姓名', 'cd_id' => '主部门'
	);

	/** 用户主表字段 */
	public $member_main = array(
		'm_mobilephone' => '手机号码', 'm_email' => '邮箱', 'm_username' => '真实姓名',
		'm_active' => '在职状态', 'm_number' => '工号', 'cd_id' => '主部门',
		'cj_id' => '担任职务', 'm_gender' => '性别', 'm_face' => '头像',
	);

	/** 用户扩展信息表字段 */
	public $member_field = array(
		'mf_address' => '住址', 'mf_idcard' => '身份证号', 'mf_telephone' => '电话号码',
		'mf_qq' => 'QQ', 'mf_weixinid' => '微信号', 'mf_birthday' => '生日',
		'mf_remark' => '其他备注'
	);

	/** 需要集合用户搜索的字段 */
	public $member_search_field = array(
		'm_mobilephone', 'm_email', 'm_username', 'mf_address', 'mf_telephone', 'mf_qq',
		'mf_remark',
	);

	/** 本地通讯录与企业微信成员字段名之间的映射关系 */
	protected $_local_to_qywx_field_map = array(
		'm_openid' => 'userid',
		'm_username' => 'name',
		'm_mobilephone' => 'mobile',
        'm_active' => 'enable',
		//'mf_telephone' => 'tel',
		'm_email' => 'email',
		'm_weixin' => 'weixinid',
		'mf_qq' => 'qq',
		'cj_id' => 'position',
		'cd_id' => 'department',
		'm_gender' => 'gender',
	);

	/** 本地性别对企业微信性别设置映射关系 */
	protected $_local2qywx_gender_map = array(
		0 => 0,// 保密=>男
		1 => 1,// 男
		2 => 2,// 女
	);

	/** 企业微信对本地性别设置映射关系 */
	protected $_qywx2local_gender_map = array(
        0 => 0,// 未设置
		1 => 1,// 男
		2 => 2,// 女
	);

	/** cookie名定义 */
	protected $_cookie_names = array(
		'uid_cookie_name' => 'uid',
		'lastlogin_cookie_name' => 'lastlogin',
		'auth_cookie_name' => 'auth'
	);

	public function __construct(){
		parent::__construct();
		if ($this->serv_member === null) {
			$this->serv_member = &service::factory('voa_s_oa_member');
			$this->serv_member_department = &service::factory('voa_s_oa_member_department');
			$this->serv_member_field = &service::factory('voa_s_oa_member_field');
			$this->serv_member_search = &service::factory('voa_s_oa_member_search');
			$this->serv_member_share = &service::factory('voa_s_oa_member_share');
			$this->serv_common_adminer = &service::factory('voa_s_oa_common_adminer');

			$sets = voa_h_cache::get_instance()->get('setting', 'oa');
			// 是否开通了企业微信
			$this->use_qywx = !empty($sets['ep_wxqy']);

			$this->setting = voa_h_cache::get_instance()->get('setting', 'oa');

			/*$domain_name = voa_h_func::get_domain();
			$this->_cookie_names = array(
				'uid_cookie_name' => $domain_name.'_member_' . $this->_cookie_names['uid_cookie_name'],
				'lastlogin_cookie_name' => $domain_name.'_member_' . $this->_cookie_names['lastlogin_cookie_name'],
				'auth_cookie_name' => $domain_name.'_member_' . $this->_cookie_names['auth_cookie_name'],
			);*/
		}
	}

	/**
	 * 生成用于登录认证的cookie验证值
	 * @param string $password
	 * @param number $uid
	 * @param string $username
	 * @param number $lastlogin
	 * @return string
	 */
	public function generate_auth($password, $uid, $lastlogin) {

		return md5($password."\t".$uid."\t".$lastlogin);
	}

	/**
	 * 检查手机号码
	 * @param string $mobilephone <strong style="color:red">(引用结果)</strong>
	 * @param string $check_exists 检查是否登记过
	 * <pre>
	 * false=不做任何登记与否的检查
	 * true=只检查是否被使用过
	 * [number]=除此uid外的其他人是否登记过
	 * </pre>
	 * @return boolean
	 */
	public function check_member_mobilephone(&$mobilephone, $check_exists = false) {

		$mobilephone = (string)$mobilephone;
		$this->_cnumber2number($mobilephone);
		$mobilephone = trim($mobilephone);

        if ($mobilephone == '') {
            //允许为空
            return true;
        }

		if (!validator::is_mobile($mobilephone)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_MOBILE_FORMAT_ERROR);
		}

		if ($check_exists) {
			$uid = is_numeric($check_exists) ? $check_exists : 0;
			if ($this->serv_member->count_by_field_not_uid('m_mobilephone', $mobilephone, $uid) > 0) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_MOBILE_USED);
			}
		}

		return true;
	}

	/**
	 * 检查邮箱
	 * @param string $email <strong style="color:red">(引用结果)</strong>邮箱地址
	 * @param string $check_exists 检查是否登记过
	 * <pre>
	 * false=不做任何登记与否的检查
	 * true=只检查是否被使用过
	 * [number]=除此uid外的其他人是否登记过
	 * </pre>
	 * @return boolean
	 */
	public function check_member_email(&$email, $check_exists = false) {

		$email = (string)$email;
		$email = trim($email);
		$email = rstrtolower($email);
        if ($email == '') {
            //允许为空
            return true;
        }
		if (!validator::is_email($email)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_EMAIL_FORMAT_ERROR);
		}

		if ($check_exists) {
			$uid = is_numeric($check_exists) ? $check_exists : 0;
			if ($this->serv_member->count_by_field_not_uid('m_email', $email, $uid) > 0) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_EMAIL_USED);
			}
		}

		return true;
	}

	/**
	 * 检查真实姓名
	 * @param string $username <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_username(&$username, $m_uid = 0) {

		$username = (string)$username;
		$username = trim($username);

		if (!validator::is_realname($username)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_USERNAME_FORMAT_ERROR);
		}

		return true;
	}

	public function check_member_password(&$passwd, $m_uid = 0) {

		$passwd = (string)$passwd;
		$passwd = trim($passwd);

		if (!validator::is_md5($passwd)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_PASSWORD_NEW_NOT_MD5);
		}

		return true;
	}

	/**
	 * 检查 openid 格式
	 * @param string $openid 用户唯一标识
	 * @param int $m_uid 用户UID
	 * @return boolean
	 */
	public function check_member_openid(&$openid, $m_uid = 0) {

		if (!preg_match('/^[a-z0-9_]+$/i', $openid)) {
			return $this->set_errmsg('1000045:账号格式错误');
		}

		return true;
	}

	/**
	 * 检查用户的主部门ID 或者 部门名称设置是否正确
	 * @param number|string $cd_id <strong style="color:red">(引用结果)</strong>部门ID
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_cd_id(&$cd_id, $m_uid = 0) {

		if (is_numeric($cd_id)) {
			// 待检查的是部门ID

			$cd_id = (int)$cd_id;

			if ($cd_id < 1) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_DEPARTMENT_NULL);
			}

			$serv_common_department = &service::factory('voa_s_oa_common_department', array());
			if (!$serv_common_department->fetch($cd_id)) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_DEPARTMENT_NOT_EXISTS, rhtmlspecialchars($cd_id));
			}

		} elseif (is_string($cd_id)) {
			// 待检查的是部门名称，部门名称禁止使用纯数字

			$cd_name = (string)$cd_id;
			$cd_name = trim($cd_name);
			if ($cd_name == '') {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_DEPARTMENT_NAME_NULL);
			}

			$serv_common_department = &service::factory('voa_s_oa_common_department', array());
			if (!($department = $serv_common_department->fetch_by_cd_name($cd_name))) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_DEPARTMENT_NAME_NOT_EXISTS);
			}

			$cd_id = $department['cd_id'];

		} else {
			// 非法数据格式
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_DEPARTMENT_FORMAT_ERROR);
		}

		return true;
	}

	/**
	 * 检查在职状态，在职状态ID 或 在职状态文字
	 * @param number|string $active <strong style="color:red">(引用结果)</strong>在职状态ID
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_active(&$active, $m_uid = 0) {

		if (is_numeric($active)) {
			// 如果是在职状态的ID值，则判断是否合法

			if (!isset($this->active_list[$active])) {
				//return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ACTIVE_ERROR);
				$active = voa_d_oa_member::ACTIVE_YES;
			}

		} elseif (is_string($active)) {
			// 如果给出的是在职状态的描述文字，则进行判断并返回对应的ID值

			$active_id = false;
			foreach ($this->active_list as $_id => $_name) {
				if (stripos($_name, $active) !== false) {
					$active_id = $_id;
					break;
				}
			}

			if ($active_id === false) {
				//return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ACTIVE_STRING_ERROR);
				$active = voa_d_oa_member::ACTIVE_YES;
			} else {
				$active = $active_id;
			}

		} else {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ACTIVE_FORMAT_ERROR);
		}

		return true;
	}

	/**
	 * 工号检查
	 * @param number $number <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_number(&$number = 0, $m_uid = 0) {

		$number = trim($number);
		$number = (int)$number;

		// 验证范围
		if ($number < 0 || $number > 999999999) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_NUMBER_ERROR);
		}

		return true;
	}

	/**
	 * 检查职务设置，职务ID或职务名称
	 * @param number|string $cj_id <strong style="color:red">(引用结果)</strong>职务ID
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_cj_id(&$cj_id, $m_uid = 0) {

		if (!$cj_id) {
			// 允许不设置职务
			$cj_id = 0;
			return true;
		}

		if (is_numeric($cj_id)) {
			// 待检查的是职务ID

			$cj_id = (int)$cj_id;

			if ($cj_id < 1) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_JOB_NULL);
			}

			$serv_common_job = &service::factory('voa_s_oa_common_job', array());
			if (!$serv_common_job->fetch($cj_id)) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_JOB_NOT_EXISTS, rhtmlspecialchars($cj_id));
			}

		} elseif (is_string($cj_id)) {
			// 待检查的是职务名称，职务名称禁止使用纯数字

			$cj_name = (string)$cj_id;
			$cj_name = trim($cj_name);
			if ($cj_name == '') {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_JOB_NAME_NULL);
			}

			$serv_common_job = &service::factory('voa_s_oa_common_job', array());
			if (!($job = $serv_common_job->fetch_by_cj_name($cj_name))) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_JOB_NAME_NOT_EXISTS);
			}

			$cj_id = $job['cj_id'];

		} else {
			// 非法数据格式
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_JOB_FORMAT_ERROR);
		}

		return true;
	}

	/**
	 * 检查性别设置，文字或性别id
	 * @param number|string $gender <strong style="color:red">(引用结果)</strong>性别ID
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_gender(&$gender, $m_uid = 0) {

		if (!$gender) {
			// 允许不设置性别
			$gender = 0;
			return true;
		}

		if (is_numeric($gender)) {
			// 待检查的是性别ID

			if (!isset($this->gender_list[$gender])) {
				// 未定义的ID
				$gender = 0;
			}

		} else {
			// 待检查的是性别文字
			$gender = (string)$gender;
			$gender = trim($gender);
			$gender_id = false;
			foreach ($this->gender_list as $_id => $_name) {
				if (stripos($gender, $_name) !== false) {
					$gender_id = $_id;
					break;
				}
			}

			$gender = false === $gender_id ? 0 : $gender_id;
		}

		return true;
	}

    public function check_member_displayorder(&$displayorder, $m_uid = 0) {
        $displayorder = trim($displayorder);
        if (is_numeric($displayorder)) {
            $displayorder = rintval($displayorder);
        } else {
            $displayorder = 0;
        }

        return true;

    }

	/**
	 * 检查住址
	 * @param string $address <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_address(&$address = '', $m_uid = 0) {

		$address = (string)$address;
		$address = trim($address);

		if ($address == '') {
			// 允许为空
			return true;
		}

		if (isset($address{250}) || rhtmlspecialchars($address) != $address) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ADDRESS_ERROR);
		}

		return true;
	}

	/**
	 * 检查身份证号
	 * @param string $idcard <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_idcard(&$idcard = '', $m_uid = 0) {

		$idcard = (string)$idcard;
		$this->_cnumber2number($idcard);
		$idcard = trim($idcard);

		if ($idcard == '') {
			// 允许为空
			return true;
		}

		if (!validator::is_id_card($idcard)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_IDCARD_ERROR);
		}

		return true;
	}

	/**
	 * 检查电话号
	 * @param string $telephone <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_telephone(&$telephone = '', $m_uid = 0) {

		$telephone = (string)$telephone;
		$this->_cnumber2number($telephone);
		$telephone = trim($telephone);

		if ($telephone == '') {
			// 允许为空
			return true;
		}

		if (!validator::is_phone($telephone)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_TELETEPHONE_ERROR);
		}

		return true;
	}

	/**
	 * 检查QQ
	 * @param string $qq <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_qq(&$qq = '', $m_uid = 0) {

		$qq = (string)$qq;
		$qq = trim($qq);

		if ($qq == '') {
			// 允许为空
			return true;
		}

		if (!validator::is_qq($qq)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_QQ_ERROR);
		}

		return true;
	}

	/**
	 * 检查微信号
	 * @param string $weixinid <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_weixinid(&$weixinid = '', $m_uid = 0) {

		$weixinid = (string)$weixinid;
		$weixinid = trim($weixinid);

		if ($weixinid == '') {
			// 允许为空
			return true;
		}

		if (strlen($weixinid) > 40) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WEIXINID_ERROR);
		}

        if ($this->serv_member->count_by_field_not_uid('m_weixin', $weixinid, $m_uid) > 0) {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WEIXINID_UESED);
        }

		return true;
	}

	/**
	 * 检查生日
	 * @param string $birthday <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_birthday(&$birthday = '', $m_uid = 0) {

		$birthday = (string)$birthday;
		$birthday = trim($birthday);

		if ($birthday == '' || $birthday == '0000-00-00') {
			// 允许为空
			$birthday = '0000-00-00';
			return true;
		}

		if (!validator::is_date($birthday)) {
			$birthday = '0000-00-00';
			return true;
		}

		return true;
	}

	/**
	 * 检查其他备注信息
	 * @param string $remark <strong style="color:red">(引用结果)</strong>
	 * @param number $m_uid
	 * @return boolean
	 */
	public function check_member_remark(&$remark = '', $m_uid = 0) {

		$remark = (string)$remark;
		$remark = trim($remark);

		if ($remark == '') {
			// 允许为空
			return true;
		}

		if (!validator::is_len_in_range($remark, 0, 255, 'utf-8') || rhtmlspecialchars($remark) != $remark) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_REMARK_ERROR);
		}

		return true;
	}

	/**
	 * 获取姓名的首字母
	 * @param string $username
	 * @param string $index <strong style="color:red">(引用结果)</strong>姓名首字母
	 * @return boolean
	 */
	public function get_username_index($username, &$index) {
		$pinyin = new pinyin();
		$index = $pinyin->to_ucwords_first($username, 4);
		return true;
	}


	/**
	 * 转换全角数字为半角数字
	 * @param string $string <strong style="color:red">(引用结果)</strong>
	 * @return boolean
	 */
	protected function _cnumber2number(&$string = '') {
		$string = str_replace(array('１', '２', '３', '４', '５', '６', '７', '８', '９', '０'),
				array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'), $string);
		return true;
	}

	/**
	 * 利用姓名来构造一个用户微信标识ID字符串
	 * @param string $realname 姓名
	 * @param string $userid <strong>(引用结果)</strong> 生成的唯一标识符userid /openid
	 * @return string
	 */
	protected function _make_userid($realname = '', &$userid = '') {
		$userid = md5(mt_rand(1, 999999).$realname.time().mt_rand(1, 999999));
		return true;
	}

	/**
	 * 构造用户详情搜索的储存数据
	 * @param array $member 用户全部信息（主表、扩展表）
	 * @param string $search_data <strong style="color:red">(引用结果)</strong>存储在用户搜索表的数据
	 * @return boolean
	 */
	protected function _make_member_search_data($member, &$search_data) {
		$data = array();
		foreach ($this->member_search_field as $key) {
			if (isset($member[$key])) {
				$data[] = $member[$key];
			}
		}
		$search_data = implode("\n", $data);
		return true;
	}

	/**
	 * 将本地数据字段 转换为企业微信接口需要的数据
	 * @param array $local 本地数据格式
	 * @param array $wxqy <strong style="color:red">(返回结果)</strong> 企业微信数据格式
	 */
	public function local_to_wxqy($local, &$wxqy) {

		$wxqy = array();
		foreach ($this->_local_to_qywx_field_map as $local_field => $wxqy_field) {
			if (!isset($local[$local_field])) {
				// 未定义数据则忽略
				continue;
			}

			if ($wxqy_field == 'department') {
				// 处理部门数据
                if (is_array($local['cd_id'])) {
                    $wxqy['department'] = array();
                    foreach ($local['cd_id'] as $cd_id) {
                        $wxqy['department'][] = $this->get_qxwxid($cd_id);
                    }
                } else {
                    $wxqy['department'] = $this->get_qxwxid($local['cd_id']);
                }

			} elseif ($wxqy_field == 'position') {
				// 处理职位数据

				if ($local['cj_id']) {
					$job = array();
					$job_uda_get = &uda::factory('voa_uda_frontend_job_get');
					$job_uda_get->job($local['cj_id'], $job);
					if (empty($job)) {
						$job_name = '';
					} else {
						$job_name = $job['cj_name'];
					}
				} else {
					$job_name = '';
				}
				$wxqy['position'] = $job_name;

			} elseif ($wxqy_field == 'gender') {
				// 处理性别数据

				$wxqy['gender'] = isset($this->_local2qywx_gender_map[$local['m_gender']]) ? $this->_local2qywx_gender_map[$local['m_gender']] : 0;

			} else {
				// 其他可直接利用的字段
				$wxqy[$wxqy_field] = $local[$local_field];
			}
		}

		if (!isset($wxqy['userid']) || empty($wxqy['userid'])) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_QYWX_USERID_NONE);
		}

		return true;
	}

    function get_qxwxid($cd_id) {
        $department = array();
        $department_uda_get = &uda::factory('voa_uda_frontend_department_get');
        $department_uda_get->department($cd_id, $department);
        if (empty($department['cd_qywxid'])) {
            // 无法获取到本地部门对应的企业微信部门的id

            // 则尝试添加
            $qywx_addressbook = new voa_wxqy_addressbook();
            $post_data = array();
            $new_department = array();
            $department_uda_update = uda::factory('voa_uda_frontend_department_update');
            $department_uda_update->local_to_wxqy($department, $post_data);

            if ($qywx_addressbook->department_create($post_data, $new_department)) {
                // 提交到微信接口获取id

                // 更新本地数据表
                $_update = array();
                $department_uda_update->update($department, array('cd_name' => $department['cd_name'], 'cd_qywxid' => $new_department['id']), $_update, true);
                $department['cd_qywxid'] = $new_department['id'];
            } else {
                return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ADD_DEPARTMENT_TO_QYWX);
            }
        }
        return $department['cd_qywxid'];
    }

    // 更新部门人数
    public function update_department_usernum() {

    	$serv_cdp = &service::factory('voa_s_oa_common_department');
    	// 统计所有部门人数
    	$serv_mdp = &service::factory('voa_s_oa_member_department');
    	$counts = $serv_mdp->list_count_by_cdid();
    	// 部门列表
    	$departments = voa_h_cache::get_instance()->get('department', 'oa');
    	// 遍历所有记录
    	foreach ($counts as $_ct) {
    		// 如果记录中用户数和实际值不符, 则更新
    		if ($departments[$_ct['cd_id']]['cd_usernum'] != $_ct['ct']) {
    			$serv_cdp->update(array('cd_usernum' => $_ct['ct']), $_ct['cd_id']);
    		}
    	}

    	// 更新总数
    	$serv_mem = &service::factory('voa_s_oa_member');
    	$count = $serv_mem->count_all();

    	foreach ($departments as $_dp) {
    		if (0 == $_dp['cd_upid']) {
    			$serv_cdp->update(array('cd_usernum' => $count), $_dp['cd_id']);
    		}
    	}

    	return true;
    }

}
