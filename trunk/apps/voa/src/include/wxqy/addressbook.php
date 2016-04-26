<?php
/**
 * voa_wxqy_addressbook
 * 微信企业/通讯录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_wxqy_addressbook extends voa_wxqy_base {

	// Userid 不存在
	const ERRCODE_USERID_IS_NOT_EXIST = 60111;
	// Userid 已存在
	const ERRCODE_USERID_IS_EXIST = 60102;
	/**
	 * 通讯录同步请求时标识HTTP协议调用的版本号
	 * @var string
	 */
	private $_bizmp_version = '1.0';

	/** 企业微信根部门id */
	public $department_parentid = 1;

	/** 请求错误信息，一般用于调试 */
	public $error_msg = '';

	/** 企业微信部门列表 */
	protected $_department_list = NULL;

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	/**
	 * <strong>企业微信的通讯录接口</strong>
	 * <p>初始化继承父类，获取access_token</p>
	 */
	public function __construct() {
		parent::__construct();

		// 获取 access_token $this->_access_token
		parent::get_access_token();
	}

	/**
	 * 部门：创建新部门
	 * @param array $data 部门数据 array(name => 部门名称, parentid => 父亲部门id)
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcod => 0, errmsg => '', id => [number])
	 * @return boolean
	 */
	public function department_create($data, &$result) {

		/**
		 * 添加新部门时，首先获取企业微信的部门列表，检查是否有同名的，如果没有则添加，有则直接返回结果信息
		 */
		if (empty($this->_department_list)) {
			// 请求列表
			$department_list_result = array();
			$this->department_list($department_list_result);
			$this->_department_list = empty($department_list_result['department']) ? array() : $department_list_result['department'];
		}

		if ($this->_department_list) {
			// 遍历检查是否存在此名的部门名称
			foreach ($this->_department_list as $d) {
				if (rstrtolower($d['name']) == rstrtolower($data['name']) && $d['parentid'] == $data['parentid']) {
					// 存在完全同名的名称，则直接返回该部门的信息，而不再重新添加
					$this->error_msg = '部门名称不能重复(errno:60008)';
					$result = array('errcode' => 60008, 'errmsg' => '部门名称不能重复', 'id' => $d['id']);
					return false;
				}
			}
		}

		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::DEPARTMENT_CREATE_URL)) {
			return false;
		}

		/** 保证上级id值正确 */
		if (isset($data['parentid'])) {
			$data['parentid'] = (int)$data['parentid'];
		}

		$data['parentid'] = isset($data['parentid']) && 0 < $data['parentid'] ? $data['parentid'] : $this->department_parentid;

		// 提交请求并获取结果
		$json_data = rjson_encode($data);
		$this->_post($result, $api_url, $json_data, 'POST', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			return false;
		}

		return true;
	}

	/**
	 * 部门：更新部门
	 * @param array $data 部门数据array(id => 部门id, name => 部门名称)
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'updated')
	 * @return boolean
	 */
	public function department_update($data, &$result) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::DEPARTMENT_UPDATE_URL)) {
			return false;
		}

		// 提交请求并获取结果
		$this->_post($result, $api_url, rjson_encode($data), 'POST', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			return false;
		}

		return true;
	}

	/**
	 * 部门：删除部门
	 * @param string $id
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'deleted')
	 * @return boolean
	 */
	public function department_delete($id, &$result) {

		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::DEPARTMENT_DELETE_URL, $id)) {
			return false;
		}

		// 提交请求并获取结果
		$this->_post($result, $api_url, array('id' => $id), 'DELETE', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			return false;
		}

		return true;
	}

	/**
	 * 部门：获取部门列表
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'ok', 'department' => array(
	 * 	array(id => 0, name=>name, parentid=>parentid), .....))
	 * @return boolean
	 */
	public function department_list(&$result) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::DEPARTMENT_LIST_URL)) {
			return false;
		}

		// 提交接口并获取结果
		$this->_post($result, $api_url, '', 'GET', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			if (60011 == $this->errcode) {
				$this->errmsg = '畅移没有权限同步, 请到微信企业号开启通讯录权限';
			} else {
				$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

				//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			}

			$this->errmsg .= "(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg."\nurl:{$api_url}");
			return false;
		}

		return true;
	}

	/**
	 * 根据部门id读取用户详情列表
	 * @param array $result 结果数组
	 * @param number $dp_id 部门id
	 * @return boolean
	 */
	public function user_list(&$result, $dp_id = 1) {

		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::USER_LIST_URL, $dp_id)) {
			return false;
		}

		$this->_post($result, $api_url, '', 'GET', true);
		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			if (60011 == $this->errcode) {
				$this->errmsg = '畅移没有权限同步, 请到微信企业号开启通讯录权限';
			}

			$this->errmsg .= "(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 根据部门id读取用户列表
	 * @param array $result 结果数组
	 * @param number $dp_id 部门id
	 * @return boolean
	 */
	public function department_simple_list(&$result, $dp_id = 1) {

		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::DEPARTMENT_SIMPLE_LIST_URL, $dp_id)) {
			return false;
		}
		$this->_post($result, $api_url, '', 'GET', true);
		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			if (60011 == $this->errcode) {
				$this->errmsg = '畅移没有权限同步, 请到微信企业号开启通讯录权限';
			}

			$this->errmsg .= "(errno:{$this->errcode})";
			$this->error_msg = isset($result['errcode']) ? $this->errmsg : 'request error';
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 成员：创建新成员
	 * @param array $data 成员数据
	 * array ( 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', )
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'created')
	 * @return boolean
	 */
	public function user_create($data, &$result, $update = true) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::USER_CREATE_URL)) {
			return false;
		}

		// 提交接口并获取结果
		$this->_post($result, $api_url, rjson_encode($data), 'POST', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			if ($update && self::ERRCODE_USERID_IS_EXIST == $result['errcode']) {
				return $this->user_update($data, $result, false);
			}

			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg.':'.$api_url.':'.var_export($data, true));
			return false;
		}

		return true;
	}

	/**
	 * 成员：更新成员
	 * @param array $data
	 * array ( 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', )
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'created')
	 * @return boolean
	 */
	public function user_update($data, &$result, $create = true) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::USER_UPDATE_URL)) {
			return false;
		}

		// 提交接口并获取结果
		$this->_post($result, $api_url, rjson_encode($data), 'POST', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			if ($create && self::ERRCODE_USERID_IS_NOT_EXIST == $result['errcode']) {
				return $this->user_create($data, $result, false);
			}

			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			$this->error_msg = $this->errmsg;
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg.':'.$api_url.':'.var_export($data, true));
			return false;
		}

		return true;
	}

	/**
	 * 成员：删除成员
	 * @param string $userid 成员唯一标识符
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'deleted')
	 * @return boolean
	 */
	public function user_delete($userid, &$result) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::USER_DELETE_URL, $userid)) {
			return false;
		}

		// 提交接口并获取结果
		$this->_post($result, $api_url, array('userid' => $userid), 'DELETE', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-100';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			$this->error_msg = $this->errmsg;
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 成员：获取成员信息
	 * @param string $userid 系统唯一标识符
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array ( 'errcode'=>0, 'errmsg'=>string, 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', 'status' => number)
	 * @return boolean
	 */
	public function user_get($userid, &$result) {
		// 接口url
		$api_url = '';
		if (!$this->_addressbook_api_url($api_url, parent::USER_GET_URL, $userid)) {
			return false;
		}

		// 提交接口并获取结果
		$this->_post($result, $api_url, '', 'GET', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
			$this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

			//$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
			if (60011 == $this->errcode) {
				$this->errmsg = '畅移没有权限同步, 请到微信企业号开启通讯录权限';
			}

			$this->errmsg .= "(errno:{$this->errcode})";
			$this->error_msg = $this->errmsg;
			// 记录错误日志
			logger::error($this->errcode.':'.$this->errmsg."(userid:{$userid};{$api_url})");
			return false;
		}

		return true;
	}

    /**
     * 成员：邀请成员关注
     * @param string $userid 成员唯一标识符
     * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
     * array(errcode => 0, errmsg => 'ok', type => 1) type=1微信邀请，2邮件邀请
     * @return boolean
     */
    public function user_invite($userid, $invite_tips = '邀请关注', &$result) {
        // 接口url
        $api_url = '';
        if (!$this->_addressbook_api_url($api_url, parent::USER_INVITE_URL)) {
            return false;
        }

        // 提交接口并获取结果
        $this->_post($result, $api_url, rjson_encode(array('userid' => $userid, 'invite_tips' => $invite_tips)), 'POST', true);

        if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
            // 请求出错
            $this->errcode = isset($result['errcode']) ? $result['errcode'] : '-1';
	        $this->errmsg = (isset($this->_api_errcodes[$this->errcode]) ? $this->_api_errcodes[$this->errcode] : '服务器繁忙')."(errno:{$this->errcode})";

	        //$this->errmsg = (isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙')."(errno:{$this->errcode})";
            $this->error_msg = $this->errmsg;
            // 记录错误日志
            logger::error($this->errcode.':'.$this->errmsg);
            return false;
        }

        return true;
    }

	/**
	 * 通讯录特定的私有请求数据方法：从指定 url 获取 json 数据
	 * @param array $data <strong style="color:red">(引用结果)</strong>结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param string $http_method 使用传输的协议 GET|POST|DELETE
	 * @param boolean $retry 是否需要重新获取
	 * @return boolean
	 */
	private function _post(&$data, $url, $post = '', $http_method = 'GET', $retry = true) {

		// 加入自定义的请求http头字段
		$http_header = array(
			'Bizmp-Version' => $this->_bizmp_version,
			'Content-Type' => 'application/json; charset=UTF-8;',
		);

		// 获取json数据
		if (!voa_h_func::get_json_by_post_and_header($data, $url, $post, $http_header, $http_method)) {
			return false;
		}

		if (!isset($data['errcode']) || 0 != $data['errcode']) {
			// 如果是请求错误，则重新尝试
			return $this->_repost($data, $url, $post, $http_method, $retry);
		} else {
			// 其他类型错误
			return false;
		}

		return true;
	}

	/**
	 * 通讯录特定的私有请求数据的方法：从指定 url 重新获取 json 数据
	 * @param array $data 结果 <strong style="color:red">(引用结果)</strong>结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	protected function _repost(&$data, $url, $post = '', $http_method = 'GET', $retry = false) {
		if (empty($this->_access_token_errcode) || !$retry
		|| !in_array($data['errcode'], $this->_access_token_errcode)) {
			return false;
		}

		$token = $this->_access_token;
		// 强制重新获取 access token
		$this->get_access_token(true);
		if ($this->_access_token == $token) {
			return false;
		}

		$data = array();
		return $this->_post($data, $url, $post, $http_method, false);
	}

	/**
	 * 用于测试是否开启了通讯录权限
	 * @param string $corpid
	 * @param string $corpsecret
	 * @return boolean
	 */
	public function addressbook_power_testing($corpid, $corpsecret) {

		$old_corpid = $this->_corp_id;
		$old_corpsecret = $this->_corp_secret;

		$this->_corp_id = $corpid;
		$this->_corp_secret = $corpsecret;

		$r = array();
		if ($this->department_list($r)) {
			$success = true;
		} else {
			$success = false;
		}

		$this->_corp_id = $old_corpid;
		$this->_corp_secret = $old_corpsecret;

		return $success;
	}

	/**
	 * 获取通讯录api的url
	 * @param string $url_string
	 * @return boolean
	 */
	protected function _addressbook_api_url(&$url_string) {
		if (parent::get_access_token() === false) {
			$this->errcode = -500;
			$this->errmsg = '您的账号未与企业号绑定，请前往应用中心进行安装绑定!';
			$this->error_msg = $this->errmsg;
			return false;
		}

		// 获取参数数组
		$params = func_get_args();
		// 获取 format 字串
		$url = $params[1];
		// 切出所有 sprintf 参数
		$params = array_slice($params, 2);
		// 把 format 字串和 access token 也推入参数
		array_unshift($params, $url, $this->_access_token);

		$url_string = call_user_func_array('sprintf', $params);
		return true;
	}
}
