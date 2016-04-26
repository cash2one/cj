<?php
/**
 * 新闻公告 权限设置 Service
 * User: Muzhitao
 * Date: 2015/9/15
 * Time: 14:21
 * Email:muzhitao@vchangyi.com
 */

namespace  News\Service;
use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class NewsSettingService extends AbstractSettingService {

	protected $_memeber_model;
	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsSetting");
		$this->_memeber_model = D('Common/MemberDepartment');
	}


	/**
	 * 判断是否有权限发布公告
	 * @param $m_uid 用户m_uid
	 * @return bool
	 */
	public function issue($m_uid) {

		// 查询并返回用户所在的部门ID
		$cd_arr = $this->_memeber_model->list_by_conds(array('m_uid' => $m_uid));
		$ac_id = array_column($cd_arr, 'cd_id');

		// 获取配置信息
		$p_setting = $this->list_kvs();

		// 判断用户ID是否存在部门数组中
		if (!empty($ac_id) && !empty($p_setting['cd_ids'])) {
			if(array_intersect($p_setting['cd_ids'], $ac_id)) {
				return true;
			}
		}

		/* 如果用户id是否存在权限数组数组中 */
		if(isset($p_setting['m_uids'])) {
			if(in_array($m_uid, $p_setting['m_uids'])) {
				return true;
			}
		}

		return false;
	}

	// 读取所有
	public function list_kvs() {

		// 查询
		$list = $this->_d->list_all();

		// 重新整合, 改成 key-value 键值对
		$sets = array();
		foreach ($list as $_v) {
			$sets[$_v['key']] = unserialize($_v['value']);
		}

		return $sets;
	}
}
