<?php
/**
 * voa_uda_frontend_addressbook_base
 * 统一数据访问/通讯录/基本控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_addressbook_base extends voa_uda_frontend_base {

	/** 真实姓名规则 */
	public $realname_rule = array('count', 1, 54);

	/** 固定电话规则 */
	public $telephone_rule = array('count', 0, 64);

	/** 本地性别对企业微信性别设置映射关系 */
	public $local2qywx_gender_map = array(
			0 => 0,// 保密=>男
			1 => 0,// 男
			2 => 1,// 女
	);

	/** 企业微信对本地性别设置映射关系 */
	public $qywx2local_gender_map = array(
			0 => 1,// 男
			1 => 2,// 女
	);

	/** 在职状态映射关系 */
	public $active_map = array(
			1 => '在职',
			0 => '离职'
	);

	/**
	 * 本地通讯录与企业微信成员字段名之间的映射关系
	 * @var array
	 */
	public $user_field_map = array(
			'm_openid' => 'userid',
			'cab_realname' => 'name',
			'cab_mobilephone' => 'mobile',
			'cab_telephone' => 'tel',
			'cab_email' => 'email',
			'cab_weixinid' => 'weixinid',
			'cab_qq' => 'qq',
			'cj_id' => 'position',
			'cd_id' => 'department',
			'cab_gender' => 'gender',
	);

	/**
	 * 通讯录与用户表字段映射关系
	 * @var array
	 */
	public $member_field_map = array(
			'cab_mobilephone' => 'm_mobilephone',
			'cab_number' => 'm_number',
			'cab_gender' => 'm_gender',
			'cd_id' => 'cd_id',
			'cj_id' => 'cj_id',
	);

	/**
	 * common_addressbook 表实例
	 * @var object
	 */
	public $serv = null;

	/**
	 * excel 的列名定义，注意排序顺序
	 * @var array
	 */
	public $excel_fields = array(
			'#' => array('name'=>'#', 'width'=>'5',),
			'cab_realname' => array('name'=>'姓名*', 'width'=>16,),
			//'cd_name' => array('name'=>'部门*', 'width'=>18,),
			'cab_mobilephone' => array('name'=>'手机号码*', 'width'=>14,),
			'cab_email' => array('name'=>'邮箱', 'width'=>30,),
			'cab_qq' => array('name'=>'QQ', 'width'=>14,),
			'cab_weixinid' => array('name'=>'微信号', 'width'=>14,),
			'cab_number' => array('name'=>'工号', 'width'=>12,),
			'cab_active' => array('name'=>'在职状态', 'width'=>11,),
			'cj_name' => array('name'=>'职位', 'width'=>16,),
			'cab_idcard' => array('name'=>'身份证号码', 'width'=>20,),
			'cab_gender' => array('name'=>'性别', 'width'=>8,),
			'cab_telephone' => array('name'=>'电话号码', 'width'=>14,),
			'cab_birthday' => array('name'=>'生日', 'width'=>11,),
			'cab_address' => array('name'=>'住址', 'width'=>30,),
			'cab_remark' => array('name'=>'备注', 'width'=>100,),
	);

	public function __construct() {
		parent::__construct();
		if ($this->serv == null) {
			$this->serv = &service::factory('voa_s_oa_common_addressbook');
		}
	}

	/**
	 * 验证 姓名 字段合法性
	 * @param string $realname <strong style="color:red">(引用结果)</strong>姓名字符串
	 * @return boolean
	 */
	public function validator_realname(&$realname, $cab_id = 0) {
		$realname = (string)$realname;
		$realname = trim($realname);
		if ($realname != rhtmlspecialchars($realname)) {
			$this->errmsg(10001, '姓名不能包含特殊字符');
			return false;
		}
		if (!$this->validator_length($realname, $this->realname_rule)) {
			$this->error = '姓名'.$this->error;
			return false;
		}
		// 检测姓名是否有重复的
		if ($this->serv->count_by_field_notid('cab_realname', $realname, $cab_id) > 0) {
			$this->errmsg(1005, '姓名不能重名，推荐添加后缀字符，比如：'.$realname.sprintf('%02s', mt_rand(1,99)));
			return false;
		}
		return true;
	}

	/**
	 * 验证手机号
	 * @param string $mobilephone <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_mobilephone(&$mobilephone, $cab_id = 0) {
		$mobilephone = (string)$mobilephone;
		$mobilephone = trim($mobilephone);

		// 输入验证
		if (!validator::is_mobile($mobilephone)) {
			$this->errmsg(10002, '手机号码错误');
			return false;
		}

		// 是否重复
		if ($this->serv->count_by_mobilephone_notid($mobilephone, $cab_id) > 0) {
			$this->errmsg(10003, '手机号码已被登记过，不能再次绑定');
			return false;
		}

		return true;
	}

	/**
	 * 验证邮箱的输入合法性
	 * @param string $email <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_email(&$email, $cab_id = 0) {
		$email = (string)$email;
		$email = trim($email);

		// 输入验证
		if (!validator::is_email($email)) {
			$this->errmsg(10004, '邮箱地址填写错误');
			return false;
		}

		// 是否重复
		if ($this->serv->count_by_field_notid('cab_email', $email, $cab_id) > 0) {
			$this->errmsg(1005, '邮箱地址已被其他人绑定，不能再次登记');
			return false;
		}

		return true;
	}

	/**
	 * 验证微信号
	 * @param string $weixinid <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_weixinid(&$weixinid, $cab_id = 0) {
		$weixinid = (string)$weixinid;
		$weixinid = trim($weixinid);

		if ($weixinid == '') {
			// 为空则忽略验证
			return true;
		}

		// 输入验证
		if (!$this->validator_length($weixinid, array('byte', 1, 40))) {
			$this->errmsg(10006, '微信号输入格式错误');
			return false;
		}

		// 是否重复
		if ($this->serv->count_by_field_notid('cab_weixinid', $weixinid, $cab_id) > 0) {
			$this->errmsg(10007, '微信号已被其他人绑定，不能再次绑定');
			return false;
		}

		return true;
	}

	/**
	 * 验证QQ号码
	 * @param string $qq <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_qq(&$qq, $cab_id = 0) {
		$qq = (string)$qq;
		$qq = trim($qq);

		// 为空则忽略验证
		if ($qq == '') {
			return true;
		}

		// 输入验证
		if (!validator::is_qq($qq)) {
			$this->errmsg(100010, 'QQ 格式输入错误');
			return false;
		}

		// 是否重复
		if ($this->serv->count_by_field_notid('cab_qq', $qq, $cab_id) > 0) {
			$this->errmsg(10007, 'QQ号已被其他人绑定，不能再次绑定');
			return false;
		}

		return true;
	}

	/**
	 * 判断整理性别信息
	 * @param number|string $gender<strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_gender(&$gender, $cab_id = 0) {
		$gender = (string)$gender;
		$gender = trim($gender);
		if (isset($this->local2qywx_gender_map[$gender])) {
			// 给定的性别是id值，且在定义内，则直接使用
			return true;
		} elseif (!is_numeric($gender)) {
			// 给定的性别值不是一个数字（不是一个合法的id值），则尝试利用文字来区分
			if (strpos($gender, '男') !== false) {
				$gender = 1;
			} elseif (strpos($gender, '女') !== false) {
				$gender = 2;
			} else {
				// 未知即未设置
				$gender = 0;
			}
		} else {
			// 无法判断传入的性别值，则认为是未知
			$gender = 0;
		}

		return true;
	}

	/**
	 * 检查并重整工号
	 * @param number $number  <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_number(&$number, $cab_id = 0) {
		$number = trim($number);
		$number = (int)$number;

		// 验证范围
		if ($number < 0 || $number > 999999999) {
			$this->errmsg(10008, '工号设置错误');
			return false;
		}

		return true;
	}

	/**
	 * 检查并重整地址信息
	 * @param string $address <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_address(&$address, $cab_id = 0) {
		$address = (string) $address;
		$address = trim($address);

		// 为空则忽略验证
		if ($address == '') {
			return true;
		}

		// 验证格式
		if (!validator::is_addr($address)) {
			$this->errmsg(10009, '住址格式填写错误');
			return false;
		}

		return true;
	}

	/**
	 * 检查身份证号码
	 * @param string $idcard <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_idcard(&$idcard, $cab_id = 0) {
		$idcard = (string)$idcard;
		$idcard = trim($idcard);

		// 为空则忽略验证
		if ($idcard == '') {
			return true;
		}

		// 验证格式
		if (!validator::is_id_card($idcard)) {
			$this->errmsg(10010, '身份证号码格式错误');
			return false;
		}

		return true;
	}

	/**
	 * 检查并整理在职状态
	 * @param string $active <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_active(&$active, $cab_id = 0) {

		if (!is_scalar($active)) {
			// 非标量则认为是在职
			$active = 1;
			return true;

		} elseif (!isset($this->active_map[$active])) {
			// 如果不是预定义的在职状态id值，则遍历映射关系查找是否包含状态文字以确定其id值

			// 默认认为是在职
			$active = 1;
			foreach ($this->active_map as $_active_id => $_active) {
				if ($_active_id > 0 && stripos($active, $_active) !== false) {
					$active = $_active_id;
					break;
				}
			}
		}

		return true;
	}

	/**
	 * 检查整理电话号码
	 * @param string $telephone <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_telephone(&$telephone, $cab_id = 0) {
		$telephone = (string)$telephone;
		$telephone = trim($telephone);

		// 为空则忽略验证
		if ($telephone == '') {
			return true;
		}

		// 验证格式
		if (!validator::is_phone($telephone)) {
			$this->errmsg(10001, '电话号码格式错误');
			return false;
		}

		return true;
	}

	/**
	 * 检查并整理备忘信息
	 * @param string $remark <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_remark(&$remark = '', $cab_id = 0) {
		$remark = (string)$remark;

		// 为空则忽略验证
		if ($remark == '') {
			return true;
		}

		// 验证长度
		if (!validator::is_len_in_range($remark, 0, 255, 'utf-8')) {
			$this->errmsg(10002, '备忘信息长度应该限制小于 255个字符以内');
			return false;
		}

		return true;
	}

	/**
	 * 检查并整理生日
	 * @param string $birthday <strong style="color:red">(引用结果)</strong>
	 * @param number $cab_id
	 * @return boolean
	 */
	public function validator_birthday(&$birthday, $cab_id = 0) {
		$birthday = (string)$birthday;
		$birthday = trim($birthday);

		// 为空则忽略验证
		if ($birthday == '') {
			$birthday = '0000-00-00';
			return true;
		}

		// 格式错误
		if ($birthday != '0000-00-00' && !validator::is_date($birthday)) {
			$birthday = '0000-00-00';
			return true;
		}

		return true;
	}

	/**
	 * 检查并尝试创建部门
	 * @param number $cd_id <strong style="color:red">(引用结果)</strong> 部门ID
	 * @param string $cd_name 部门名称
	 * @param number $cab_id
	 * @return boolean
	 */
	public function check_department(&$cd_id, $cd_name = '') {
		$department_uda_base = &uda::factory('voa_uda_frontend_department_base');
		if (!$department_uda_base->check_department($cd_id, $cd_name, $cd_id)) {
			$this->errmsg(1003, '部门必须填写');
			return false;
		}

		return true;
	}

	/**
	 * 检查并尝试创建职位
	 * @param number $cj_id <strong>(应用结果)</strong> 职位ID
	 * @param string $cj_name 职位名称
	 * @return boolean
	 */
	public function check_job(&$cj_id = 0, $cj_name = '') {
		$job_uda_base = &uda::factory('voa_uda_frontend_job_base');
		if (!$job_uda_base->check_job($cj_id, $cj_name, $cj_id)) {
			return false;
		}

		return true;
	}

}
