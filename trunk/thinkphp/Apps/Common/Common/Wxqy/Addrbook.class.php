<?php
/**
 * Addrbook.class.php
 * 微信企业/通讯录
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class Addrbook {

	// 创建成员
	const USER_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=%s';
	// 更新成员
	const USER_UPDATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=%s';
	// 删除成员
	const USER_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=%s&userid=%s';
	// 批量删除
	const USER_BATCH_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=%s';
	// 获取成员
	const USER_GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=%s&userid=%s';
	// 获取部门的成员列表
	const USER_SIMPLE_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%d&fetch_child=%d&status=%d';
	// 获取部门成员列表(详情)
	const USER_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=%s&department_id=%s&fetch_child=%d&status=%d';
	// 邀请关注
	const USER_INVITE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/invite/send?access_token=%s';
	// 创建部门
	const DEPARTMENT_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=%s';
	// 更新部门
	const DEPARTMENT_UPDATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=%s';
	// 删除部门
	const DEPARTMENT_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=%s&id=%s';
	// 获取部门列表
	const DEPARTMENT_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=%s';

	/**
	 * 通讯录同步请求时标识HTTP协议调用的版本号
	 * @var string
	 */
	private $_bizmp_version = '1.0';
	// 企业微信根部门id
	public $root_parentid = 1;
	// 企业微信部门列表
	protected $_departments = NULL;
	// 自定义的请求http头字段
	protected $_http_header = array();
	// service 方法
	protected $_serv;

	/**
	 * <strong>企业微信的通讯录接口</strong>
	 * <p>初始化继承父类，获取access_token</p>
	 */
	public function __construct(&$serv) {

		$this->_serv = $serv;
		$this->_http_header = array(
			'Bizmp-Version' => $this->_bizmp_version,
			'Content-Type' => 'application/json; charset=UTF-8;',
		);
	}

	/**
	 * 批量删除用户
	 * @param array $userids 待删除的企业号 USERID 数组
	 * @return boolean
	 */
	public function user_batch_delete_url($userids) {

		// API url
		$url = self::USER_BATCH_DELETE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 读取指定部门的用户列表
		$result = array();
		if (!$this->__post($result, $url, array('useridlist' => $userids), array(), 'POST')) {
			return false;
		}

		return true;
	}

	/**
	 * 获取指定部门的成员列表, 只有 userid(m_openid) 和 name(m_username)
	 * @param array $result 用户信息
	 * @param number $dp_id 部门id
	 * @param number $status 关注状态, 0: 获取全部成员; 1: 获取已关注成员列表; 2: 获取禁用成员列表; 4: 获取未关注成员列表; status可叠加
	 * @param number $fetch_child 是否递归获取子部门下面的成员, 1: 是; 2: 否
	 * @return boolean
	 */
	public function user_simple_list(&$result, $dp_id = 1, $status = 0, $fetch_child = 1) {

		// API url
		$url = self::USER_SIMPLE_LIST_URL;
		if (!$this->_serv->create_token_url($url, $dp_id, $fetch_child, $status)) {
			return false;
		}

		// 读取指定部门的用户列表
		$result = array();
		if (!$this->__post($result, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取指定部门的成员列表(详情)
	 * @param array $result 用户信息
	 * @param number $dp_id 部门id
	 * @param number $status 关注状态, 0: 获取全部成员; 1: 获取已关注成员列表; 2: 获取禁用成员列表; 4: 获取未关注成员列表; status可叠加
	 * @param number $fetch_child 是否递归获取子部门下面的成员, 1: 是; 2: 否
	 * @return boolean
	 */
	public function user_list(&$result, $dp_id = 1, $status = 0, $fetch_child = 1) {

		// API url
		$url = self::USER_LIST_URL;
		if (!$this->_serv->create_token_url($url, $dp_id, $fetch_child, $status)) {
			return false;
		}

		// 读取指定部门的用户列表
		$result = array();
		if (!$this->__post($result, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 邀请指定用户关注
	 * @param array $result 返回结果
	 * @param string $userid 企业号的USERID
	 */
	public function user_invite(&$result, $userid) {

		// 获取邀请的 API URL
		$url = self::USER_INVITE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 提交接口并获取结果
		$result = array();
		if (!$this->__post($result, $url, array('userid' => $userid), array(), 'POST')) {
			return false;
		}

		return true;
	}

	/**
	 * 部门: 创建新部门
	 * @param array $data 部门数据 array(name => 部门名称, parentid => 父亲部门id)
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * @return boolean
	 */
	public function department_create(&$result, $data) {

		// 添加新部门时，首先获取企业微信的部门列表，检查是否有同名的，如果没有则添加，有则直接返回结果信息
		if (empty($this->_departments)) {
			// 请求列表
			$departments = array();
			if (!$this->department_list($departments)) {
				return false;
			}

			$this->_departments = empty($departments['department']) ? array() : $departments['department'];
		}

		// 如果部门列表存在
		if ($this->_departments) {
			// 遍历检查是否存在此名的部门名称
			foreach ($this->_departments as $_d) {
				if (rstrtolower($_d['name']) == rstrtolower($data['name']) && $_d['parentid'] == $data['parentid']) {
					// 存在完全同名的名称，则直接返回该部门的信息，而不再重新添加
					E(L('_ERR_WX_DEPARTMENT_DUPLICATE', array('name' => $_d['name'])));
					return false;
				}
			}
		}

		// 接口url
		$url = self::DEPARTMENT_CREATE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 保证上级id值正确
		if (isset($data['parentid'])) {
			$data['parentid'] = (int)$data['parentid'];
			// 如果上级ID为 0, 则取根部门的ID
			if (0 == $data['parentid']) {
				$data['parentid'] = $this->root_parentid;
			}
		} else {
			$data['parentid'] = $this->root_parentid;
		}

		// 提交请求并获取结果
		if (!$this->__post($result, $url, rjson_encode($data), array(), 'POST')) {
			return false;
		}

		return true;
	}

	/**
	 * 部门: 更新部门
	 * @param array $data 部门数据array(id => 部门id, name => 部门名称)
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * @return boolean
	 */
	public function department_update(&$result, $data) {

		// 接口url
		$url = self::DEPARTMENT_UPDATE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 提交请求并获取结果
		if (!$this->__post($result, $url, rjson_encode($data), array(), 'POST')) {
			return false;
		}

		return true;
	}

	/**
	 * 部门: 删除部门
	 * @param string $id
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * @return boolean
	 */
	public function department_delete(&$result, $id) {

		// 接口url
		$url = self::DEPARTMENT_DELETE_URL;
		if (!$this->_serv->create_token_url($url, $id)) {
			return false;
		}

		// 提交请求并获取结果
		$data = array('id' => $id);
		if (!$this->__post($result, $url, rjson_encode($data), array(), 'DELETE')) {
			return false;
		}

		return true;
	}

	/**
	 * 部门: 获取部门列表
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * @return boolean
	 */
	public function department_list(&$result) {

		// 接口url
		$url = self::DEPARTMENT_LIST_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 提交接口并获取结果
		$result = array();
		if (!$this->__post($result, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 成员: 创建新成员
	 * @param array $data 成员数据
	 * array ( 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', )
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'created')
	 * @return boolean
	 */
	public function user_create(&$result, $data, $update = true) {

		// 接口url
		$url = self::USER_CREATE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 提交接口并获取结果
		$result = array();
		if (!$this->_serv->post($result, $url, rjson_encode($data), array(), 'POST') || !isset($result['errcode']) || 0 != $result['errcode']) {
			if ($update && cfg('WX_API_ERRCODE_60102') == $result['errcode']) {
				return $this->user_update($result, $data, false);
			}

			// 记录日志
			Log::record('url: '.$url.'; result: '.var_export($result, true));
			// 根据错误号报错
			E(L('_ERR_WX_SERVER_BUSY'));

			return false;
		}

		return true;
	}

	/**
	 * 成员: 更新成员
	 * @param array $data
	 * array ( 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', )
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'created')
	 * @return boolean
	 */
	public function user_update(&$result, $data, $create = true) {

		// 接口url
		$url = self::USER_UPDATE_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 提交接口并获取结果
		$result = array();
		if (!$this->_serv->post($result, $url, rjson_encode($data), array(), 'POST')) {
			if ($create && cfg('WX_API_ERRCODE_60111') == $result['errcode']) {
				return $this->user_create($result, $data, false);
			}

			// 记录日志
			Log::record('url: '.$url.'; result: '.var_export($result, true));
			// 根据错误号报错
			E(L('_ERR_WX_SERVER_BUSY'));

			return false;
		}

		return true;
	}

	/**
	 * 成员: 删除成员
	 * @param string $userid 成员唯一标识符
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array(errcode => 0, errmsg => 'deleted')
	 * @return boolean
	 */
	public function user_delete(&$result, $userid) {

		// 接口url
		$url = self::USER_DELETE_URL;
		if (!$this->_serv->create_token_url($url, $userid)) {
			return false;
		}

		// 提交接口并获取结果
		$data = array('userid' => $userid);
		if (!$this->__post($result, $url, rjson_encode($data), array(), 'DELETE')) {
			return false;
		}

		return true;
	}

	/**
	 * 成员: 获取成员信息
	 * @param string $userid 系统唯一标识符
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的结果
	 * array ( 'errcode'=>0, 'errmsg'=>string, 'userid' => 'zhangsan', 'name' => 'string',
	 * 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
	 * 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
	 * 'string', 'qq' => 'number', 'status' => number)
	 * @return boolean
	 */
	public function user_get(&$result, $userid) {

		// 接口url
		$url = self::USER_GET_URL;
		if (!$this->_serv->create_token_url($url, $userid)) {
			return false;
		}

		// 提交接口并获取结果
		if (!$this->__post($result, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 用于测试是否开启了通讯录权限
	 * @param string $corpid 企业 corpid
	 * @param string $corpsecret 企业 corpsecret
	 * @return boolean
	 */
	public function addressbook_power_testing($corpid, $corpsecret) {

		// 切换企业号信息
		$this->_serv->toggle_corp($corpid, $corpsecret);
		$result = array();
		if ($this->department_list($result)) {
			$success = true;
		} else {
			$success = false;
		}

		// 切回企业号
		$this->_serv->toggle_corp();
		return $success;
	}

	private function __post(&$result, $url, $post = '', $headers = array(), $method = 'GET', $retry = true) {

		// 如果参数为数组
		if (is_array($post)) {
			$post = rjson_encode($post);
		}

		// 如果头信息为空, 则取默认
		if (empty($headers)) {
			$headers = $this->_http_header;
		}

		// 提交请求并获取结果
		$result = array();
		if (!$this->_serv->post($result, $url, $post, $headers, $method, $retry)) {
			// 记录日志
			Log::record(var_export(array('url' => $url, 'post' => $post, 'headers' => $headers, 'method' => $method, 'result' => $result), true));
			// 判断返回值
			if (cfg('WX_API_ERRCODE_60011') == $result['errcode']) { // 无通讯录权限
				E(L('_ERR_API_NO_ADDRBOOK_PERMISSION'));
			} else {
				E(L('_ERR_WX_SERVER_BUSY'));
			}

			return false;
		}

		return true;
	}
}
