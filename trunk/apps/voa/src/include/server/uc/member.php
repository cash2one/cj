<?php
/**
 * 会员信息接口
 * $Author$
 * $Id$
 */

class voa_server_uc_member {
	/** openid 已存在 */
	const OPENID_EXISTS = -1;
	/** 用户名有关键字 */
	const USERNAME_BADWORD = -2;
	/** 用户名已存在 */
	const USERNAME_EXISTS = -3;

    /**
     * __construct
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * 注册
     * @param array $args 用户信息
     *  + openid 用户openid
     *  + username 用户名
     *  + password 密码
     *  + gender 性别
     */
    public function register($args) {
    	$openid = trim($args['openid']);
    	$username = trim($args['username']);
    	$passwd = trim($args['password']);
    	$gender = intval($args['gender']);
    	/** 如果 openid 为空 */
    	if (empty($openid)) {
    		throw new rpc_exception('openid is empty', 100);
    	}

    	$serv = &service::factory('voa_s_uc_member', array('pluginid' => 0));
    	/** 判断 openid 是否存在 */
    	$user = $serv->fetch_by_openid($openid);
    	if (!empty($user)) {
    		return $user['m_uid'];
    	}

    	/** 入库 */
    	$uid = $serv->insert(array(
    		'm_openid' => $openid,
    		'm_username' => $username,
    		'm_password' => $passwd,
    		'm_gender' => $gender
    	), true);

    	return $uid;
    }

    /**
     * 获取用户信息
     * @param array $args 参数
     *  + uid 用户uid
     *  + openid 用户openid
     */
    public function get($args) {
    	$serv = &service::factory('voa_s_uc_member', array('pluginid' => 0));
    	$openid = trim($args['openid']);
    	if ($openid) {
    		return $serv->fetch_by_openid($openid);
    	}

    	$username = trim($args['username']);
    	if ($username) {
    		//return $serv->fetch_by_username($username);
    	}

    	$uid = intval($args['uid']);
    	if ($uid) {
    		return $serv->fetch_by_uid($uid);
    	}

    	throw new rpc_exception('user is not exists', 100);
    }
}
