<?php
/**
 * 公司信息操作接口
 * $Author$
 * $Id$
 */

class voa_server_uc_company {
	/** domain 已存在 */
	const DOMAIN_EXISTS = -1;
	/** 用户名有关键字 */
	const DOMAIN_BADWORD = -2;

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
     *  + company 公司名称
     *  + domain 二级域名
     *  + wechat 微信号
     *  + realname 真实姓名
     *  + idcard 身份证号
     *  + qq QQ
     *  + email email
     *  + mobilephone 手机号码
     *  + telephone 固定号码
     *  + address 地址
     */
    public function register($args) {
    	if (empty($args) || empty($args['company']) || empty($args['domain'])) {
    		throw new rpc_exception('company or domain empty', 100);
    	}

    	$company = trim($args['company']);
    	$domain = trim($args['domain']);

    	$serv = &service::factory('voa_s_uc_company', array('pluginid' => 0));
    	/** 判断 openid 是否存在 */
    	$data = $serv->fetch_by_domain($domain);
    	if (!empty($data)) {
    		throw new rpc_exception('domain is exists', 100);
    	}

    	/** 取当前企业 oa 的数据库服务器/端口 */
    	$dbhost = '127.0.0.1';
    	$dbport = 3306;
    	$dbpw = random(16);
    	/** 入库 */
    	$cid = $serv->insert(array(
    		'c_company' => $company,
    		'c_domain' => $domain,
    		'c_wechat' => trim($args['wechat']),
    		'c_username' => trim($args['username']),
    		'c_password' => trim($args['password']),
    		'c_realname' => trim($args['realname']),
    		'c_idcard' => trim($args['idcard']),
    		'c_qq' => trim($args['qq']),
    		'c_email' => trim($args['email']),
    		'c_mobilephone' => trim($args['mobilephone']),
    		'c_telephone' => trim($args['telephone']),
    		'c_address' => trim($args['address']),
    		'c_dbhost' => $dbhost,
    		'c_dbport' => $dbport,
    		'c_dbpw' => $dbpw
    	), true);

    	return array('dbhost' => $dbhost, 'dbport' => $dbport, 'dbpw' => $dbpw, 'cid' => $cid);
    }
}
