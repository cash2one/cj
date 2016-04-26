<?php
/**
 * getuserlist.php
 * 通过uid获取用户信息列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_getuserlist extends voa_uda_frontend_member_base {

	/** 请求的参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他内部参数 */
	private $__option = array();

	/** 待查询的uid数组 */
	private $__uids = array();
    /** 分库/分表的信息 */
    private $__shard_key = array();

    /**
     * __construct
     *
     * @param  array $shard_key
     * @return void
     */
    public function __construct($shard_key = array()) {

        $this->__shard_key = $shard_key;
    }
	/**
	 * 获取给定uid或者一组uid的人员信息
	 * @param array $request 请求的参数
	 * + uid 查询单人(非必须)
	 * + uids 查询多人，一组uid列表(非必须)
	 * # 如果两个参数同时给出，则会合并。
	 * @param array $result (引用结果)返回结果，以uid为键名的原始数据
	 * @param array $option 其他内部配置参数
	 * @return boolean
	 */
	public function doit($request, &$result = array(), $option = array()) {

		// 赋值内部成员
		$this->__option = $option;
		// 定义字段规则
		$fields = array(
			'uid' => array('uid', parent::VAR_INT, null, null, true),
			'uids' => array('uids', parent::VAR_ARR, null, null, true)
		);
		// 基本过滤检查
		if (!$this->extract_field($this->__request, $fields, $request, true)) {
			return false;
		}

		// 查询单人
		if (isset($this->__request['uid'])) {
			$this->__uids[] = $this->__request['uid'];
		}
		// 查询多人
		if (isset($this->__request['uids'])) {
			$this->__uids = array_merge($this->__uids, $this->__request['uids']);
		}

		// 重新整理uids，确保无重复，无小于0的
		$_uids = rintval($this->__uids, true);
		$this->__uids = array();
		foreach ($_uids as $_uid) {
			if (!isset($this->__uids[$_uid]) && $_uid > 0) {
				$this->__uids[$_uid] = $_uid;
			}
		}
		// 判断是否为空
		if (empty($this->__uids)) {
			return array();
		}
		// 读取列表
		$this->__result = voa_h_user::get_multi($this->__uids);
		// 返回列表结果
		$result = $this->__result;
		foreach ($result as &$_m) {
			$_m['_avator'] = voa_h_user::avatar($_m['m_uid'], $_m);
		}

		return true;
	}
    /**
     * 列出指定uid的用户
     * @param array $uids
     * @throws service_exception
     * @return array
     */
    public function fetch_all_by_ids($uids) {
        try {
            return voa_s_oa_member::fetch_all_by_ids($uids, $this->__shard_key);
        } catch (Exception $e) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 【S】读取所有
     * @author Deepseath
     * @param int $start
     * @param int $limit
     * @throws service_exception
     */
    public function fetch_all($start = 0, $limit = 0) {
        try {
            return voa_d_oa_member::fetch_all($start, $limit, $this->__shard_key);
        } catch (Exception $e) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 【S】读取所有
     * @author Deepseath
     * @param int $uid
     * @param int $start
     * @param int $limit
     * @throws service_exception
     */
    public function fetch_all_purview($uid,$start = 0, $limit = 0) {
        //获取最大权限部门
        $serv_depart = &service::factory('voa_s_oa_common_department');
        $type= $serv_depart->fetch_purview($uid);
        var_dump($type);exit;
        //根据部门获取信息

    }
}
