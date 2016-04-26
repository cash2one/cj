<?php
/**
 * MemberService.class.php
 * $author$
 */
namespace Common\Service;

use Com\Pinyin;
use Common\Service\AbstractService;
use Common\Common\Wxqy\Addrbook;

class MemberService extends AbstractService {

	/** 微信接口接收数据类型 */
	protected $_wxjk_rule = array();
	/** 微信数据 和 数据库字段匹配 */
	protected $_data_field = array();
	/** 人员部门关联表Model */
	protected $_mem_dep_model = null;
	/** 人员属性扩展表 */
	protected $_member_field = array();
	/** 自定义属性数量 */
	const EXT_NUM = 10;
	/** 微信企业状态未关注 */
	const QYWXSTATUS_NO = 4;
	/** 在职 */
	const ACTIVE = 1;
	/** 规则 (开启) */
	const ALLOW = 1;
	/** 规则 (关闭) */
	const UNALLOW = 0;
	/** 人员性别 */
	const MALE = 1;
	const G_UNKNOWN = 0;
	const FMALE = 2;
	const C_MALE = '男';
	const C_G_UNKNOWN = '未知';
	const C_FMALE = '女';

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/Member');
	}

	/**
	 * 根据 $openid 读取用户信息
	 * @param string $openid openid
	 * @return boolean
	 */
	public function get_by_openid($openid) {

		// 读取用户信息
		return $this->_d->get_by_openid($openid);
	}

	/**
	 * 根据 openid 读取列表
	 * @param array $openids openid 数组
	 */
	public function list_by_openids($openids) {

		return $this->_d->list_by_openids($openids);
	}

	/**
	 * 统计小于当前更新时间的人数
	 * @param $updated
	 * @return mixed
	 */
	public function count_less_than_updated($updated) {

		return $this->_d->count_less_than_updated($updated);
	}

	/**
	 * 读取小于更新时间的数据
	 * @param $updated
	 * @param $limit
	 * @return mixed
	 */
	public function list_less_than_updated($updated, $limit) {

		return $this->_d->list_less_than_updated($updated, $limit);
	}

	/**
	 * 根据用户名称头字母索引和部门id搜索
	 * @param string $index 关键字
	 * @param int|array $cdids 部门ID
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_index_cdids($index, $cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		if (empty($index) || empty($cdids)) {
			E('_ERR_D_PARAMS_ERROR');
			return false;
		}

		return $this->_d->list_by_index_cdids($index, $cdids, $page_option, $order_option);
	}

	/**
	 * 根据用户名索引和部门id统计总数
	 * @param string $index 用户名索引
	 * @param array $cdids 部门id
	 */
	public function count_by_index_cdids($index, $cdids) {

		return $this->_d->count_by_index_cdids($index, $cdids);
	}

	/**
	 * 根据用户名索引搜索
	 * @param string $index 关键字
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_index($index, $page_option, $order_option = array('m_index' => 'ASC')) {

		return $this->_d->list_by_index($index, $page_option, $order_option);
	}

	/**
	 * 根据用户名索引统计总数
	 * @param int $index 用户名索引
	 */
	public function count_by_index($index) {

		return $this->_d->count_by_index($index);
	}

	/**
	 * 根据名字获取
	 * @param       $username
	 * @param array $page_option
	 * @return mixed
	 */
	public function list_by_username($username, $page_option = array()) {

		return $this->_d->list_by_username($username, $page_option);
	}


	/**
	 * 添加人员
	 * @param $post
	 * @param $member
	 * @return bool
	 */
	public function add_member($post, &$member) {

		$this->_deal_edit_add_post($post, $push_data);

		// 前置赋值操作
		$this->_add_edit_member_execute();

		// 用户唯一标识
		if (empty($push_data['userid'])) {
			$this->_make_userid($push_data['name'], $push_data['userid']);
		} else {
			// 判断是否存在
			$this->_had_userid($push_data['name'], $push_data['userid']);
		}

		// 判断三样不得同时为空的数据
		if (empty($push_data['mobile']) && empty($push_data['weixinid']) && empty($push_data['email'])) {
			E('_ERR_MOBILE_WEIXINID_EMAIL_CANNOT_ALL_EMPTY');

			return false;
		}
		// 判断手机是否重复
		if (!empty($push_data['mobile']) && !$this->_had_mobilephone($push_data['mobile'])) {
			E('_ERR_MOBILE_IS_RECUR');

			return false;
		}

		// 获取微信扩展属性
		$this->_wxqy_extattr($push_data, $extattr);

		// 转换本地部门为微信部门ID
		$this->_wxqydep_to_localdep($push_data, $local_dep);

		// 赋值扩展属性
		$push_data['extattr']['attrs'] = $extattr;
		// 同步微信
		$qywx = &\Common\Common\Wxqy\Service::instance();
		$addrbook = new Addrbook($qywx);
		try {
			$addrbook->user_create($member, $push_data);
		} catch (\Think\Exception $e) {

			if ($member['errcode'] > 0) {
				$errmsg = $member['errmsg'];
				$errcode = $member['errcode'];
				E($errcode . ':' . $errmsg);

				return false;
			} elseif ($member['errcode'] < 0) {
				E('_ERR_WX_SERVER_BUSY');

				return false;
			}
		}

		// 部门转换为本地部门ID
		$push_data['department'] = $local_dep;
		// 获取写入member的数据
		$member_insert = array();
		foreach ($push_data as $_key => $_value) {
			if (isset($this->_data_field[$_key])) {
				$member_insert[$this->_data_field[$_key]] = $_value;
			}
		}

		// 获取职位信息
		$this->_get_cj_id($member_insert);
		// 生成姓名首字母
		$this->_make_name_index($member_insert['m_username'], $member_insert['m_index']);
		// 生成用户密码
		$this->_make_user_password($member_insert);

		if (!empty($member_insert['cd_id'])) {
			// 取出部门关联需要的ID
			$mem_dep = $member_insert['cd_id'];
			// 人员默认部门
			$member_insert['cd_id'] = $member_insert['cd_id'][0];
		}

		// 写入member表数据
		$member_insert['m_facetime'] = NOW_TIME;
		$member_insert['m_active'] = self::ACTIVE;
		$member_insert['m_qywxstatus'] = self::QYWXSTATUS_NO;
		$uid = $this->_d->insert($member_insert);

		// 获取写入扩展表的数据
		$field_insert = array();
		foreach ($push_data as $_key => $_value) {
			foreach ($this->_member_field as $_field) {
				if (isset($push_data[$_field])) {

					// 如果是 leader
					if ($_field == 'leader') {
						$implode = implode(',', $push_data[$_field]);
						if (empty($implode)) {
							continue;
						}
						$field_insert['mf_' . $_field] = $implode;

						continue;
					}
					$field_insert['mf_' . $_field] = $push_data[$_field];
				}
			}
		}
		$field_insert['m_uid'] = $uid;
		$serv_field = D('Common/MemberField', 'Service');
		$serv_field->insert($field_insert);

		// 写入搜索表
		$this->_member_search($member_insert, $uid);

		// 写入部门关联表
		if (!empty($mem_dep)) {
			$this->_insert_all_mem_dep($uid, $mem_dep);
		}

		return true;
	}

	/**
	 * 编辑人员
	 * @param $post
	 * @param $member
	 * @return bool
	 */
	public function edit_member($post, &$member) {

		$this->_deal_edit_add_post($post, $push_data);
		$push_data['m_uid'] = $post['m_uid'];

		// 前置赋值操作
		$this->_add_edit_member_execute();

		// 判断必须值
		if (empty($push_data['m_uid'])) {
			return false;
		}

		// 判断手机是否重复
		if (!empty($push_data['mobile']) && !$this->_had_mobilephone($push_data['mobile'], $push_data['m_uid'])) {
			return false;
		}

		// 获取用户唯一标示
		$user_data = $this->_d->get_by_conds(array('m_uid' => $push_data['m_uid']));
		if (empty($user_data)) {
			return false;
		}
		$push_data['userid'] = $user_data['m_openid'];

		// 获取微信扩展属性
		$this->_wxqy_extattr($push_data, $extattr);
		// 转换本地部门为微信部门ID
		$this->_wxqydep_to_localdep($push_data, $local_dep);
		$push_data['extattr']['attrs'] = $extattr;

		// 更新至微信
		$qywx = &\Common\Common\Wxqy\Service::instance();
		$addrbook = new Addrbook($qywx);

		$addrbook->user_update($member, $push_data);
		unset($push_data['userid']);

		// 部门转换为本地部门ID
		$push_data['department'] = $local_dep;
		// 更新member的数据
		$member_update = array();
		foreach ($push_data as $_key => $_value) {
			if (isset($this->_data_field[$_key])) {
				$member_update[$this->_data_field[$_key]] = $_value;
			}
		}

		// 获取职位信息
		$this->_get_cj_id($member_update);

		// 生成姓名首字母
		if (isset($member_update['m_username'])) {
			$this->_make_name_index($member_update['m_username'], $member_update['m_index']);
		}

		if (!empty($member_update['cd_id'])) {
			// 取出部门关联需要的ID
			$mem_dep = $member_update['cd_id'];
			// 人员默认部门
			$member_update['cd_id'] = $member_update['cd_id'][0];

			// 删除之前的人员部门关联表数据
			$this->_mem_dep_model->delete_by_conds(array('m_uid' => $post['m_uid']));

			// 写入部门关联表
			$mem_dep_insert = array();
			foreach ($mem_dep as $_dep) {
				$mem_dep_insert[] = array(
					'm_uid' => $push_data['m_uid'],
					'cd_id' => $_dep,
				);
			}
			$this->_mem_dep_model->insert_all($mem_dep_insert);
		}

		// 更新search
		$this->_member_search($member_update, $push_data['m_uid'], true);
		// 更新表数据
		unset($member_update['cj_name']);
		$this->_d->update_by_conds(array('m_uid' => $push_data['m_uid']), $member_update);

		// 获取写入扩展表的数据
		$field_insert = array();
		foreach ($push_data as $_key => $_value) {
			foreach ($this->_member_field as $_field) {
				if (isset($push_data[$_field])) {

					// 如果是 leader
					if ($_field == 'leader') {
						$field_insert['mf_' . $_field] = implode(',', $push_data[$_field]);

						continue;
					}
					$field_insert['mf_' . $_field] = $push_data[$_field];
				} else {
					// 为空
					$field_insert['mf_' . $_field] = '';
				}
			}
		}

		if (!empty($field_insert)) {
			$serv_field = D('Common/MemberField');
			$serv_field->update_by_conds(array('m_uid' => $push_data['m_uid']), $field_insert);
		}

		return true;
	}

	/**
	 * 删除人员
	 * @param string $openid 人员唯一标识
	 * @param int    $m_uid 人员ID
	 * @return bool
	 */
	public function delete_member($openid, $m_uid) {

		// 提交到微信
		$qywx = &\Common\Common\Wxqy\Service::instance();
		$addrbook = new Addrbook($qywx);
		if (!$addrbook->user_batch_delete_url($openid)) {
			return false;
		}

		try {
			// 删除member表数据
			$serv_mem = D('Common/Member');
			$serv_mem->delete_by_conds(array('m_uid' => $m_uid));
			// 删除用户信息
			$serv_mem_field = D('Common/MemberField');
			$serv_mem_field->delete_by_conds(array('m_uid' => $m_uid));
			// 删除部门关联数据
			$serv_mem_dep = D('Common/MemberDepartment');
			$serv_mem_dep->delete_by_conds(array('m_uid' => $m_uid));
			// 删除搜索表数据
			$serv_mem_search = D('Common/MemberSearch');
			$serv_mem_search->delete_by_conds(array('m_uid' => $m_uid));
			// 删除用户信息分享数据
			$serv_mem_share = D('Common/MemberShare');
			$serv_mem_share->delete_by_conds(array('m_uid' => $m_uid));
			// 刪除部门领导关联表数据
			$serv_add_dep_connect = D('Common/CommonDepartmentConnect', 'Service');
			$serv_add_dep_connect->delete_by_conds(array('m_uid' => $m_uid));
			// 删除标签关联表数据
			$serv_label_member = D('Common/CommonLabelMember', 'Service');
			$serv_label_member->delete_by_conds(array('m_uid' => $m_uid));
		} catch (\Exception $e) {
			\Think\Log::record(var_export($e, true));

			return false;
		}


		return true;
	}

	/**
	 * 利用姓名来构造一个用户微信标识ID字符串
	 * @param string $realname 姓名
	 * @param string $userid <strong>(引用结果)</strong> 生成的唯一标识符userid /openid
	 * @return string
	 */
	protected function _make_userid($realname = '', &$userid = '') {

		$userid = md5(mt_rand(1, 999999) . $realname . time() . mt_rand(1, 999999));

		// 判断是否重复
		$this->_had_userid($realname, $userid);

		return true;
	}

	/**
	 * 判断userid 是否存在, 若存在重新生成 再判断
	 * @param $name
	 * @param $userid
	 * @return bool
	 */
	protected function _had_userid($name, &$userid) {

		if ($this->_d->get_by_conds(array('m_openid' => $userid))) {
			// 重新生成
			$this->_make_userid($name, $userid);
		}

		return true;
	}

	/**
	 * 处理提交的本地部门id为微信部门id
	 * @param array $data 提交的数据
	 * @param array $local_dep 本地部门ID
	 * @return bool
	 */
	protected function _wxqydep_to_localdep(&$data, &$local_dep) {

		// 获取部门缓存
		$this->_department_list($department);
		$local = array();
		foreach ($data['department'] as $_id) {
			$local[] = $department[$_id];
		}

		// 存储提交的本地部门ID
		$local_dep = $data['department'];

		// 取出微信端部门ID
		$wxqy_dep = array_column($local, 'cd_qywxid');

		// 转换为数字类型
		foreach ($wxqy_dep as &$_int) {
			$_int = (int)$_int;
		}
		$data['department'] = $wxqy_dep;

		return true;
	}

	/**
	 * 人员搜索表
	 * @param array      $member 人员数据
	 * @param int        $uid 人员id
	 * @param bool|false $update 是否是更新
	 * @return bool
	 */
	protected function _member_search($member, $uid, $update = false) {

		$serv_search = D('Common/MemberSearch');

		$ms_message = array(
			$member['m_username'],
			empty($member['cj_name']) ? '' : $member['cj_name'],
			empty($member['m_mobilephone']) ? '' : $member['m_mobilephone'],
			empty($member['m_email']) ? '' : $member['m_email'],
			empty($member['m_weixin']) ? '' : $member['m_weixin'],
		);

		if ($update) {
			$ms_message = implode("\n", $ms_message);
			$serv_search->update_by_conds(array('m_uid' => $uid), array('ms_message' => $ms_message));
		} else {
			$ms_message = array(
				'ms_message' => implode("\n", $ms_message),
				'm_uid' => $uid,
			);
			$serv_search->insert($ms_message);
		}

		return true;
	}

	/**
	 * 判断手机号码是否已经存在
	 * @param string $phone 手机号
	 * @param null   $m_uid 人员ID
	 * @return bool
	 */
	protected function _had_mobilephone($phone, $m_uid = null) {

		if (empty($phone)) {
			return false;
		}
		$cond['m_mobilephone'] = $phone;

		if (!empty($m_uid)) {
			$cond['m_uid !'] = $m_uid;
		}

		$result = $this->_d->get_by_conds($cond);
		if (!empty($result)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取姓名的首字母
	 * @param string $username
	 * @param string $index <strong style="color:red">(引用结果)</strong>姓名首字母
	 * @return boolean
	 */
	protected function _make_name_index($username, &$index) {

		$pin = new Pinyin\Pinyin();
		$index = $pin->to_ucwords_first($username);

		return true;
	}

	/**
	 * 生成用户密码
	 * @param $member
	 */
	protected function _make_user_password(&$member) {

		$password = 'vchangyi';
		//使用手机后6位为密码
		if (!empty($member['m_mobilephone'])) {
			$password = md5(substr($member['m_mobilephone'], - 6));
		} //使用邮箱为密码
		elseif (!empty($member['m_email'])) {
			$password = md5($member['m_email']);
		} //使用微信id为密码
		elseif (!empty($member['m_weixin'])) {
			$password = md5($member['m_weixin']);
		}
		list($member['m_password'], $member['m_salt']) = generate_password($password, null, false);

	}

	/**
	 * 写入人员部门关联表
	 * @param int   $uid 人员ID
	 * @param array $mem_dep
	 * + int 部门ID
	 * @return bool
	 */
	protected function _insert_all_mem_dep($uid, $mem_dep) {

		$mem_dep_insert = array();

		if (is_array($mem_dep)) {
			foreach ($mem_dep as $_dep) {
				$mem_dep_insert[] = array(
					'm_uid' => $uid,
					'cd_id' => $_dep,
				);
			}
			$this->_mem_dep_model->insert_all($mem_dep_insert);
		}

		return true;
	}

	/**
	 * 处理添加人员 和编辑人员的提交数据键值
	 * @param $post
	 * @return bool
	 */
	protected function _deal_post_data(&$post) {

		foreach ($this->_post_wxjk_rule as $_name => $_to_name) {
			if (isset($post[$_name])) {
				$post[$_to_name] = $post[$_name];
			}
		}

		return true;
	}

	/**
	 * 添加和编辑人员前置赋值
	 * @return bool
	 */
	protected function _add_edit_member_execute() {

		// 微信接口接收数据类型
		$this->_wxjk_rule = array(
			'userid' => 'string',
			'name' => 'string',
			'department' => 'array',
			'position' => 'string',
			'mobile' => 'string',
			'gender' => 'int',
			'email' => 'string',
			'weixinid' => 'string',
		);

		// 微信数据 和 数据库字段匹配
		$this->_data_field = array(
			'userid' => 'm_openid',
			'name' => 'm_username',
			'department' => 'cd_id',
			'position' => 'cj_id',
			'mobile' => 'm_mobilephone',
			'gender' => 'm_gender',
			'email' => 'm_email',
			'weixinid' => 'm_weixin',
		);

		// 写入人员扩展信息
		$this->_member_field = array(
			'leader',
			'birthday',
			'address',
		);
		for ($i = 1; $i <= self::EXT_NUM; $i ++) {
			$this->_member_field[] = 'ext' . $i;
		}

		// 人员部门关联表Model
		$this->_mem_dep_model = D('Common/MemberDepartment');

		return true;
	}

	/**
	 * 根据部门id搜索人员
	 * @param $cds array 部门id
	 * @param $status int 微信关注状态
	 * @return array
	 */
	public function count_by_cdid_status($cds, $status) {

		return $this->_d->count_by_cdid_status($cds, $status);
	}

	/**
	 * 根据部门id和关注状态搜索数据
	 * @param $cds array 部门id
	 * @param $status int 关注状态
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页条件
	 * @return mixed
	 */
	public function list_by_cdid_status($cds, $status, $limit, $page_option) {

		$mem_dep_list = $this->_d->list_by_cdid_status($cds, $status, $limit, $page_option);
		//格式返回m_uid
		$uid_list = array();
		if (!empty($mem_dep_list)) {
			foreach ($mem_dep_list as $val) {
				$uid_list[] = $val['m_uid'];
			}
		}

		return $uid_list;
	}

	/**
	 * 根据关注状态返回导出数据
	 * @param $status int 用户关注状态
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页条件
	 * @return array $uid_list
	 */
	public function list_by_conds_dump($status, $limit, $page_option) {

		$mem_list = $this->_d->list_by_conds_dump($status, $limit, $page_option);
		//格式返回m_uid
		$uid_list = array();
		if (!empty($mem_list)) {
			foreach ($mem_list as $val) {
				$uid_list[] = $val['m_uid'];
			}
		}

		return $uid_list;
	}

	/**
	 * 添加和编辑 人员用 获取职位ID
	 * @param $user_data
	 * @return bool
	 */
	protected function _get_cj_id(&$user_data) {

		if (!empty($user_data['cj_id'])) {
			$serv_cj = D('Common/CommonJob');
			$cj_data = $serv_cj->get_by_conds(array('cj_name' => $user_data['cj_id']));

			// 如果没有这个职位,那么添加
			if (empty($cj_data)) {
				$cj_id = $serv_cj->insert(array('cj_name' => $user_data['cj_id']));
				$user_data['cj_name'] = $user_data['cj_id'];
				$user_data['cj_id'] = $cj_id;
			} else {
				$user_data['cj_name'] = $user_data['cj_id'];
				$user_data['cj_id'] = $cj_data['cj_id'];
			}
		} else {
			$user_data['cj_name'] = '';
			$user_data['cj_id'] = 0;
		}

		return true;
	}

	/**
	 * 根据微信号/手机号/邮箱/userid来读取记录
	 * @param array $conds
	 * @return mixed
	 */
	public function list_by_unique_field($conds) {

		return $this->_d->list_by_unique_field($conds);
	}

	/**
	 * 获取部门列表方法
	 * @param $department
	 * @return bool
	 */
	protected function _department_list(&$department) {

		$cache = &\Common\Common\Cache::instance();
		$department = $cache->get('Common.department');

		return true;
	}

	/**
	 * 判断编辑人员和 添加人员的提交数据 是否符合设置的属性规则
	 * @param array $post 提交的数据
	 * @param array $result 结果数据
	 * @return bool
	 */
	protected function _deal_edit_add_post($post, &$result) {

		$field = $this->_get_field();

		// 规则为空
		if (empty($field)) {
			E('_ERR_MISS_FIELD');
			return false;
		}

		$result = array();
		// 判断 属性 必填值 和 赋值返回数组
		$must_one = false; // 手机号 微信号 邮箱 三选一必填
		foreach ($field as $_fix_or_custom => $_rule) {
			foreach ($_rule as $_kw => $_details) {
				if ($_kw == 'mobile' || $_kw == 'weixinid' || $_kw == 'email') {
					$temp = trim($post[$_kw]);
					if ($must_one == false) {
						$must_one = !empty($temp) ? true : false;
					}
					if ($_kw == 'weixinid' && !empty($post[$_kw]) && !preg_match('/^[a-z][\w_-]{5,19}$/i', $post[$_kw])) {
						E('_ERR_WEIXINID');
						return false;
					}
					// 验证手机号
					if ($_kw == 'mobile' && !empty($post[$_kw]) && !preg_match('/^1[\d]{10}$/', $post[$_kw])) {
						E('_ERR_MOBILE');
						return false;
					}
					if ($_kw == 'email' && !empty($post[$_kw]) && !preg_match('/^([\w_\.\-])+\@(([\w\-])+\.)+([\w]{2,4})+$/', $post[$_kw])) {
						E('_ERR_EMAIL');
						return false;
					}
				} elseif (empty($post[$_kw]) && $_details['open'] == self::ALLOW && $_details['required'] == self::ALLOW) {

					// 判断 开启 并且必填的属性 是否有提交
					E(L('_ERR_MISS_REQUIRED_VALUE', array('name' => $_details['name'])));
					return false;
				}

				// 赋值
				if (!empty($post[$_kw])) {
					$result[$_kw] = $post[$_kw];
				}
			}
		}

		// 性别
		if (is_string($result['gender'])) {
			switch($result['gender']) {
				case self::C_MALE:
					$result['gender'] = self::MALE;
					break;
				case self::C_FMALE:
					$result['gender'] = self::FMALE;
					break;
				default:
					$result['gender'] = self::G_UNKNOWN;
			}
		} else if (is_int($result['gender'])) {
			switch($result['gender']) {
				case self::MALE:
					$result['gender'] = self::MALE;
					break;
				case self::FMALE:
					$result['gender'] = self::FMALE;
					break;
				default:
					$result['gender'] = self::G_UNKNOWN;
			}
		} else {
			$result['gender'] = self::G_UNKNOWN;
		}

		// 生日
		if (!empty($result['birthday']) && is_string($result['birthday'])) {
			$result['birthday'] = str_replace('/', '-', $result['birthday']);
		}

		if (!$must_one) {
			E('_ERR_MOBILE_WEIXINID_EMAIL_CANNOT_ALL_EMPTY');
			return false;
		}

		return true;
	}

	/**
	 * 获取提交给微信的扩展数据
	 * @param array $data 提交的数据
	 * @param array $result 扩展数据
	 * @return bool
	 */
	protected function _wxqy_extattr($data, &$result) {

		$field = $this->_get_field();
		$custom = $field['custom'];

		// 获取属性和名称关联数组
		$custom_field = array();
		foreach ($custom as $_key => $_value) {
			$custom_field[$_key] = $_value['name'];
		}

		// 匹配字段名称和值
		$result = array();
		if (!empty($custom_field)) {
			unset($custom_field['leader']); // 去掉直属领导
			foreach ($custom_field as $_key => $_name) {
				if (isset($data[$_key])) {
					$result[] = array(
						'name' => $_name,
						'value' => $data[$_key],
					);
				}
			}
		}

		return true;
	}

	/**
	 * 获取人员属性规则
	 * @return array|mixed
	 */
	protected function _get_field() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		return $setting['fields'];
	}

	/**
	 * 根据 $field 读取用户自定义
	 *
	 * @param string $field
	 * @return boolean
	 */
	public function get_row_by_field() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		$res = array();

		foreach ($setting['fields']['custom'] as $k => $v) {
			if(in_array($k, array('position', 'leader', 'birthday', 'address'))) {
				continue;
			}
			$i = substr($k, 3);

			if (isset($setting['fields']['custom']['ext' . $i])) {
				$res[] = 'mf_ext' . $i;
				continue;
			}

			$res[] = 'mf_' . $k;

		}

		$res = implode(",", $res);
		return $res;
	}

	/**
	 * 读取自定义字段-及值
	 *
	 * @return boolean
	 */
	public function get_custom_by_field($uid) {

		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		$res = array();

		foreach ($setting['fields']['custom'] as $k => $v) {
			$i = substr($k, 3);

			if (isset($setting['fields']['custom']['ext' . $i])) {
				$res[] = 'mf_ext' . $i;
				continue;
			}
		}
		$res = implode(",", $res);
		if ($res) {
			$f_result = $this->_field->get_list_by_uid($uid, $res);
		}

		$f_res = array();
		foreach ($setting['fields']['custom'] as $_k => $_v) {
			if(in_array($_k, array('position', 'leader', 'birthday', 'address', 'mark', 'area', 'years', 'nickname'))) {
				continue;
			}
			$i = substr($_k, 3);
			if (isset($setting['fields']['custom']['ext' . $i])) {
				$f_res[$i]['column'] = 'mf_ext' . $i;
				$f_res[$i]['name'] = $_v['name'];
				$f_res[$i]['value'] = $f_result['mf_ext' . $i];
			}
		}
		$f_res = array_values($f_res);

		return $f_res;
	}

	/**
	 * 读取自定义字段
	 *
	 * @return boolean
	 */
	public function get_custom() {

		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		$f_res = array();
		foreach ($setting['fields']['custom'] as $_k => $_v) {
			if(in_array($_k, array('position', 'leader', 'birthday', 'address'))) {
				continue;
			}
			$i = substr($_k, 3);
			if (isset($setting['fields']['custom']['ext' . $i])) {
				$f_res[$_v['priority']]['column'] = 'mf_ext' . $_k;
				$f_res[$_v['priority']]['name'] = $_v['name'];
				continue;
			}

			$f_res[$_v['priority']]['column'] = 'mf_' . $_k;
			$f_res[$_v['priority']]['name'] = $_v['name'];

		}

		$f_res = array_values($f_res);

		return $f_res;
	}

	/**
	 * 根据 $field 读取用户信息
	 *
	 * @param string $field
	 * @return boolean
	 */
	public function count_by_field() {

		// 获取部门缓存数据
		$cache = &Cache::instance();
		$result = $cache->get('Common.member_setting');

		$count = 0;
		foreach ($result['fields']['custom'] as $_k => $_v) {
			if(in_array($_k, array('position', 'leader', 'birthday', 'address'))) {
				continue;
			}
			if ($_v['open'] == 1) {
				$count ++;
			}
		}
		return $count;
	}

	/**
	 * 根据 $cj_id 读取职位名称
	 *
	 * @param string $cj_id
	 * @return boolean
	 */
	public function get_by_cj_id($uid) {

		// 读取职位名称
		$result = $this->_job->list_by_uid($uid);
		foreach ($result as $_k => $_v) {
			$mp_id = $_v['mp_id'];
		}
		$job = $this->_position->get_member_position($mp_id);
		foreach ($job as $_k => $_v) {
			$jobname = $_v['mp_name'];
		}

		return $jobname;
	}

	/**
	 * 根据 $uid 读取用户信息
	 *
	 * @param string $uid
	 * @return boolean
	 */
	public function get_list_by_uid($uid, $setting) {

		$this->_mark = D('Community/CommunityMarkDictionary');
		// 读取用户信息
		$result = $this->_field->get_list_by_uid($uid, $setting);
		$mark_data = $this->_mark->list_by_all();
		$data = array();
		$mdid_array = array();
		$field_array = array();
		if ($result) {
			foreach ($result as $k => $v) {
				if ($k == 'mf_mark') {
					$mdid_array = unserialize($v);
					continue;
				}
				$data[$k] = $v;
			}
		} else {
			$setting = explode(',', $setting);
			foreach($setting as $v) {
				$data[$v] = '';
			}
		}

		// 格式化系统标签是键名=>键值eg:array('2'=>2,'3'=>3)
		if (isset($mdid_array['tag_system'])) {
			foreach ($mdid_array['tag_system'] as $v) {
				$field_array[$v] = $v;
			}
		}
		// 格式化系统标签选中
		$mark_data_new = array();
		$mark_choise = array();
		if ($mark_data) {
			foreach ($mark_data as &$v) {
				$mark_data_new[$v['mdid']] = $v;
			}
			$i = 0;
			foreach ($mark_data_new as $_k => &$v) {
				if (isset($field_array[$_k])) {
					$mark_choise[$v['mdid']] = $v;
					$mark_choise[$v['mdid']]['sequence'] = $i;
					$mark_choise[$v['mdid']]['is_choise'] = "1";
				}
				$i ++;
			}
		}
		if (strpos($setting, 'mf_mark') !== false) {
			$data['tag_system'] = array_values($mark_choise);
		}
		if (strpos($setting, 'mf_mark') !== false) {
			$data['tag_custom'] = isset($mdid_array['tag_custom']) ? $mdid_array['tag_custom'] : array();
		}

		return $data;
	}

	/**
	 * 获取用户兴趣标签信息
	 *
	 * @param $uid
	 * @return mixed
	 */
	public function get_mark_by_uid($uid) {

		$this->_mark = D('Community/CommunityMarkDictionary');
		$result = $this->_field->get_list_by_uid($uid, 'mf_mark');
		$mark_data = $this->_mark->list_by_all();

		$mdid_array = unserialize($result['mf_mark']);

		// 格式化系统标签是键名=>键值eg:array('2'=>2,'3'=>3)
		if (isset($mdid_array['tag_system'])) {
			foreach ($mdid_array['tag_system'] as $v) {
				$field_array[$v] = $v;
			}
		}

		// 格式化判断系统标签是否选中
		$mark_data_new = array();
		if ($mark_data) {
			$i = 0;
			foreach ($mark_data as $v) {
				$mark_data_new[$v['mdid']] = $v;
				$mark_data_new[$v['mdid']]['sequence'] = $i;
				$mark_data_new[$v['mdid']]['is_choise'] = "0";
				$i ++;
			}
			foreach ($mark_data_new as $_k => &$_v) {
				if (isset($field_array[$_k])) {
					$mark_data_new[$_v['mdid']]['is_choise'] = "1";
				}
			}
		}
		$data['tag_system'] = array_values($mark_data_new);
		$data['tag_custom'] = isset($mdid_array['tag_custom']) ? $mdid_array['tag_custom'] : array();

		return $data;
	}

	/**
	 * 根据 $uid 读取用户信息
	 *
	 * @param string $uid
	 * @return boolean
	 */
	public function get_by_uid($uid) {

		// 读取用户信息
		return $this->_d->get_by_uid($uid);
	}

	/**
	 *
	 * @param $phone 读取用户
	 * @return mixed
	 */
	public function get_by_phone($phone) {

		return $this->_d->get_by_phone($phone);
	}

	/**
	 * 根据手机号码获取用户信息
	 *
	 * @param string $mobile 手机号码
	 * @throws service_exception
	 */
	public function fetch_by_mobilephone($mobile) {

		return $this->_d->fetch_by_mobilephone($mobile);
	}

	/**
	 * 根据uid删除用户
	 *
	 * @param unknown $uid
	 */
	public function delete_by_uid($uid) {

		return $this->_d->delete_by_uid($uid);
	}


    /**
     * 根据关键字统计总数
     * @param mixed  $cd_ids 部门ID
     * @param string $kws 搜索关键字(用户名)
     * + string keyword 关键字
     * + string keyindex 索引
     * @return boolean
     */
    public function count_by_cdid_kws($cd_ids, $kws = array()) {
        return $this->_d->count_by_cdid_kws($cd_ids, $kws);
    }

    /**
     * 根据关键字搜索
     * @param mixed  $cd_ids 部门ID
     * @param string $kws 搜索关键字(用户名)
     * + string keyword 关键字
     * + string keyindex 索引
     * @param string $page_option
     * @param array  $order_option 排序
     * @return boolean
     */
    public function list_by_cdid_kws($cd_ids, $kws = array(), $page_option = null, $order_option = array()) {
        return $this->_d->list_by_cdid_kws($cd_ids, $kws, $page_option, $order_option);
    }

}
