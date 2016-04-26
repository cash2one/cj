<?php
/**
 * voa_uda_frontend_member_get
 * 统一数据访问/用户表/获取
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_get extends voa_uda_frontend_member_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 通过m_uid获取用户表信息
     * @param number $uid
     * @param array $member <strong style="color:red">(引用结果)</strong> 用户信息
     * @param boolean $get_field 是否读取用户扩展信息
     * @return boolean
     */
    public function member_by_uid($uid, &$member = array(), $get_field = false) {

        // 如果 uid 为空, 则读取
        if ($uid <= 0 || !($member = $this->serv_member->fetch($uid))) {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_NOT_EXISTS);
        }

        // 如果需要读取用户扩展信息
        if ($get_field) {
            // 读取用户扩展信息
            $member_field = $this->serv_member_field->fetch_by_id($uid);
            if (empty($member_field)) {
                $member_field = $this->serv_member_field->fetch_all_field();
            }

            $member = array_merge($member_field, $member);
        }

        return true;
    }

    /**
     * 获取用户的默认数据
     * @param array $member <strong style="color:red">(引用结果)</strong>用户默认数据
     * @return boolean
     */
    public function member_default_data(&$member) {

        $member = $this->serv_member->fetch_all_field();
        $member_field = $this->serv_member_field->fetch_all_field();
        $member = array_merge($member_field, $member);
        return true;
    }

    /**
     * 通过登录帐号（手机号、邮箱）获取用户表信息
     * @param string $account 登录帐号（手机号、邮箱等）
     * @param array $member <strong style="color:red">(引用结果)</strong> 用户信息
     * @param string $get_field 是否读取用户扩展信息
     * @return boolean
     */
    public function member_by_account($account, &$member = array(), $get_field = false) {
        $method = '';
        if (validator::is_email($account)) {
            $method = 'fetch_by_email';
        } elseif (validator::is_mobile($account)) {
            $method = 'fetch_by_mobilephone';
        } else {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ACCOUNT_ERROR);
        }
        if (!($member = $this->serv_member->$method($account))) {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_ACCOUNT_NOT_EXISTS, rhtmlspecialchars($account));
        }

        if ($member['m_status'] == voa_d_oa_member::STATUS_REMOVE) { // 用户被标记为删除状态
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_FORBID);
        }

        if ($get_field) {
            // 读取用户扩展信息
            $member_field = $this->serv_member_field->fetch_by_id($member['m_uid']);
            if (empty($member_field)) {
                $member_field = $this->serv_member_field->fetch_all_field();
            }
            $member = array_merge($member_field, $member);
        }

        return true;
    }

    /**
     * 自cookie中读取用户认证字符串信息
     * @param array $cookie_data <strong style="color:red">（引用结果）</strong>cookie信息
     * - uid 用户uid
     * - auth 认证字符串
     * - lastlogin 上次登录时间
     * @param object $session session操作对象
     * @param array $cookie_names cookie名定义
     * 默认：array(
     *  'uid_cookie_name' => 'uid',// 保存uid的cookie名
     *  'lastlogin_cookie_name' => 'lastlogin',// 保存最后登录时间的cookie名
     *  'auth_cookie_name' => 'auth'// 保存认证字符串的cookie名
     * );
     * @return boolean
     */
    public function member_auth_by_cookie(&$cookie_data, $session, $cookie_names = array()) {

        if (empty($cookie_names)) {
            $cookie_names = $this->_cookie_names;
        }

        // 一些变态的意外处理
        if (is_array($_COOKIE)) {
            foreach ($_COOKIE as $key => $value) {
                $key = str_ireplace('Cookie:_', '', $key);
                $_COOKIE[$key] = $value;
            }
        }

        // uid非法
        $uid = (int)$session->get($cookie_names['uid_cookie_name']);
        if ($uid <= 0) {
            return false;
        }

        // 最后登录时间 - 登录时写入的
        $lastlogin = (int)$session->get($cookie_names['lastlogin_cookie_name']);
        // 如果超过一天未更新, 则需要重新登录
        if ($lastlogin + 86400 < startup_env::get('timestamp')) {
        	return false;
        }

        // 认证字符串
        $auth = (string)$session->get($cookie_names['auth_cookie_name']);

        $cookie_data = array(
            'uid' => $uid,
            'lastlogin' => $lastlogin,
            'auth' => $auth
        );

        return true;
    }

    /**
     * 自cookie认证字符串信息读取用户信息
     * @param number $uid
     * @param string $auth
     * @param number $lastlogin
     * @param array $member <strong style="color:red">（引用结果）</strong>用户信息
     * @return boolean
     */
    public function member_info_by_cookie($uid, $auth, $lastlogin, &$member) {

			// 获取当前用户信息
		$member = array();
		if (! $this->member_by_uid($uid, $member, false)) {
			return false;
		}

		if ($this->generate_auth($member['m_password'], $uid, $lastlogin) != $auth) {
			return false;
		}

		$session = &session::get_instance();
		$session->set($this->_cookie_names['lastlogin_cookie_name'], startup_env::get('timestamp'));
		$session->set($this->_cookie_names['auth_cookie_name'], $this->generate_auth($member['m_password'], $uid, startup_env::get('timestamp')));
		startup_env::set('wbs_uid', $member['m_uid']);
        startup_env::set('wbs_username', $member['m_username']);
        unset($member);

        return true;
    }

    public function member_by_wechatid($wechatid, &$member = array()) {

        if (empty($wechatid)) {
            return false;
        }

        $member = $this->serv_member->fetch_by_wechatid($wechatid);
        if (empty($member)) {
            return false;
        }

        return true;
    }

    /**
     * 找到指定微信unionid的用户信息
     * @param string $unionid
     * @param array $member <strong style="color:red">（引用结果）</strong>返回指定用户信息
     * @return boolean
     */
    public function member_by_unionid($unionid, &$member = array()) {

        if (empty($unionid)) {
            return false;
        }

        $member = $this->serv_member->fetch_by_unionid($unionid);
        if (empty($member)) {
            return false;
        }

        return true;
    }

    /**
     * 列出指定条件的用户列表，无条件则按顺序列出
     * @param array $search_by
     * @param array $orderby 排序方式
     * <pre>$orderby = array(
     * 		'field1' => asc|desc,
     * 		'field2' => asc|desc
     * 		... ...
     * )</pre>
     * @param number $page 当前页码
     * @param number $limit 每页显示数
     * @param array $result <strong style="color:red;">（引用结果）</strong>结果集合
     * <pre>$result = array('page', 'limit', 'total', 'pages', 'multi', 'conditions', 'list')</pre>
     * @return boolean
     */
    public function member_search($search_by = array(), $orderby = array(), $page = 1, $limit = 50, &$result) {

        $defaults = array(
            'm_username' => '',// 真实姓名
            'm_index' => '',// 姓名索引字母
            'm_mobilephone' => '',// 手机号码
            'm_email' => '',// email
            'm_active' => -1,// 在职状态
            'm_admincp' => -1,// 是否为管理员
            'm_gender' => -1,// 性别
            'm_number' => -1,// 工号
            'cj_id' => -1,// 职务 number
            'cd_id' => -1,// 部门 number | array
            'm_uid' => array(),// 指定uid
        );

        if (empty($orderby)) {
            $orderby = array('m_uid' => 'DESC');
        }

        // 基本的条件过滤，筛选出指定查询的条件
        $filter = array();
        foreach ($defaults as $k => $v) {
            if (!isset($search_by[$k]) || $v == $search_by[$k]) {
                // 未指定此条件 或 搜索值与默认值一致，则跳过此条件
                continue;
            }
            if ($k != 'cd_id') {
                if (is_scalar($search_by[$k])) {
                    $filter[$k] = $search_by[$k];
                }
            } else {
                // 部门字段
                if (!$search_by[$k]) {
                    continue;
                }
                $tmp = array();
                if (is_array($search_by[$k])) {
                    // 试图查询多个部门
                    foreach ($search_by[$k] as $_cd_id) {
                        if (is_numeric($_cd_id) && !isset($tmp[$_cd_id])) {
                            $tmp[$_cd_id] = $_cd_id;
                        }
                    }
                } elseif (strpos($search_by[$k], ',') !== false) {
                    // 试图查询以“,”分隔的部门ID
                    foreach (explode(',', $search_by[$k]) as $_cd_id) {
                        if (is_numeric($_cd_id) && !isset($tmp[$_cd_id])) {
                            $tmp[$_cd_id] = $_cd_id;
                        }
                    }
                } elseif (is_numeric($search_by[$k])) {
                    // 试图只查询指定的部门ID
                    $tmp[$search_by[$k]] = $search_by[$k];
                }
                if ($tmp) {
                    $filter['cd_id'] = array($tmp);
                    unset($tmp);
                }
            }
        }

        // 是否查询指定uid
        if (isset($filter['m_uid'])) {
            $filter['m_uid'] = is_array($filter['m_uid']) ? $filter['m_uid'] : array($filter['m_uid'] => $filter['m_uid']);
        }

        // 检查在职状态是否存在
        if (isset($filter['m_active']) && !isset($this->active[$filter['m_active']])) {
            unset($filter['m_active']);
        }

        // 如果指定了部门则查询该部门下的人员
        if (isset($filter['cd_id'])) {

        }

        // 用于查询的条件集合
        $conditions = array();
        foreach ($filter as $key => $value) {
            if ($key == 'cd_id') {
                // 不处理具体的部门查询
                continue;
            }
            if (in_array($key, array('m_username', 'm_mobilephone', 'm_email'))) {
                // 使用模糊查询
                $conditions[$key] = array('%'.addcslashes($value, '%_').'%', 'like');
            } elseif (in_array($key, array('cd_id'))) {
                // 使用集合查询
                $conditions[$key] = array($value, 'in');
            } elseif ($key == 'm_index') {
                // 索引字母比较特殊
                $conditions[$key] = array($value.'%', 'like');
            } else {
                // 其他条件按等于查询
                $conditions[$key] = $value;
            }
        }

        // 结果列表
        $list = array();
        // 结果总数
        $total = $this->serv_member->count_by_conditions($filter);
        // 分页信息
        $multi = '';
        // 总页码
        $pages = 0;

        if ($total > 0) {
            $pager_options = array();
            if ($limit > 0) {
                $pager_options = array(
                    'total_items' => $total,
                    'per_page' => $limit,// 每页显示个数
                    'current_page' => $page,// 当前页码
                    'show_total_items' => true,
                );
                $multi = pager::make_links($pager_options);
                pager::resolve_options($pager_options);
            } else {
                $pager_options = array(
                    'start' => 0,
                    'per_page' => 0
                );
            }
            $member = $this->serv_member->fetch_all_by_conditions($filter, $orderby, $pager_options['start'], $pager_options['per_page']);
            $uda_fmt = &uda::factory('voa_uda_frontend_member_format');
            if ($member) {
                // 读取扩展字段信息
                $member_field = $this->serv_member_field->fetch_by_ids(array_keys($member));
                $member_field_default = $this->serv_member_field->fetch_all_field();
                foreach ($member as $_uid => $_m) {
                    $mf = isset($member_field[$_uid]) ? $member_field[$_uid] : $member_field_default;
                    $m_mf = array_merge($mf, $_m);
                    $uda_fmt->format($m_mf);
                    $list[] = $m_mf;
                    unset($mf);
                }
                unset($member_field, $member_field_default);
            }
            unset($member);

            if ($limit > 0) {
                $pages = ceil($total / $limit);
            } else {
                $pages = 1;
            }
        }
        $search_by = array($defaults, $search_by, $filter);
        $result = array($page, $limit, $total, $pages, $multi, $search_by, $list);

        return true;
    }


    /**
     * 根据用户id获取下级所有用户id
     * @param $m_uid 用户id
     * @param $m_uids &引用 用户id集合
     */
    public function sub_muids_by_muid($m_uid, &$m_uids) {
        $m_uid = rintval($m_uid);

        if ($m_uid < 1) {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_NOT_EXISTS);
        }
        //判断用户是否存在
        $member = $this->serv_member->fetch($m_uid);
        if(empty($member)) {
            return $this->set_errmsg(voa_errcode_oa_member::MEMBER_NOT_EXISTS);
        }
        //获取用户关联部门
        $serv_md = &service::factory('voa_s_oa_member_department');
        $cd_ids = $serv_md->fetch_all_field_by_uid($m_uid);

        if (empty($cd_ids)) {
            $this->errcode = '30000001';
            $this->errmsg = '用户没有关联部门';
            return false;
        }

        $uda_mp = &uda::factory('voa_uda_frontend_member_position');
        $uda_dg = &uda::factory('voa_uda_frontend_department_get');
        $conditions = array();
        foreach ($cd_ids as $cd) {
            $mp_ids = array();
            $sub_cd_ids = $uda_dg->get_sub_dp_ids($cd['cd_id']);
            //判断职能读取下级职位
            if ($cd['mp_id'] > 0) {
                $mp_ids = $uda_mp->get_sub_position_ids($cd['mp_id']);
            }
            //读取当前部门下级职务条件
            if (!empty($mp_ids)) {
				array_push($mp_ids, 0);
                $condi_positons['cd_id'] = $cd['cd_id'];
                $condi_positons['mp_id'] = array($mp_ids, 'IN');
                array_push($conditions, $condi_positons);

            }
            //读取下级部门条件
            if (!empty($sub_cd_ids)) {
                $condi_sub_cdid['cd_id'] = array($sub_cd_ids, 'IN');
                array_push($conditions, $condi_sub_cdid);
            }
        }

	    if (!empty($conditions)) {
	        //执行多条SQL
	        $m_uids = $serv_md->fetch_muid_multi_sql_union($conditions);
	    }
        if (empty($m_uids)) {
            $m_uids = array();
        } else {
            $m_uids = array_column($m_uids, 'm_uid');
        }

		//加入自身id
	    array_push($m_uids, $m_uid);
        return true;
    }


}
