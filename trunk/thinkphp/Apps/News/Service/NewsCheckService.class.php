<?php
/**
 * 公告审核 Service
 * User: Muzhitao
 * Date: 2015/9/25 0025
 * Time: 10:18
 * Email：muzhitao@vchangyi.com
 */

namespace  News\Service;

class NewsCheckService extends AbstractService {

	protected $_memeber_model;
	// 构造方法
	public function __construct() {

		parent::__construct();

		// 实例化相关模型
		$this->_d = D("News/NewsCheck");
		$this->_memeber_model = D('Common/Member');
	}

	/**
	 * 判断当前公告审核的状态
	 * @param $data
	 * @return bool
	 */
	public function is_check($data) {

		if ($data['is_check'] == self::IS_CHECK) {
			return false;
		}

		return true;
	}

	/**
	 * 格式化预览公告
	 * @param $detail
	 */
	public function format_check(&$detail) {

		// 查询用户详情
		$user_data = $this->_memeber_model->get($detail['m_uid']);

		// 删除不相关字段
		unset($detail['m_uid']);
		unset($detail['is_publish']);

		$detail['username'] = $user_data['m_username'];
	}

	/**
	 * 预览回复 更新审核信息
	 * @param $conds
	 * @param $params
	 * @return bool
	 */
	public function update_data($data, $params) {

		if ($data) {
			// 如果当前审核状态是3 则返回已经审核过了
			if ($data['is_check'] == self::IS_CHECK) {
				return false;
			}

			// 更新审核信息
			$this->_d->update($data['nec_id'], $params);
		}

		return true;
	}

	/**
	 * 查询公告审核信息
	 * @param $ne_id
	 * @param $m_uid
	 * @return mixed
	 */
	public function did_show($ne_id, $m_uid) {

		return $this->_d->get_check_by_ne_id($ne_id, $m_uid);
	}
}
