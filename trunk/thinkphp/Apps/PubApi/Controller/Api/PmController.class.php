<?php
/**
 * PmController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

class PmController extends AbstractController {

	/**
	 * 根据用户统计未读记录总数
	 * @return bool
	 */
	public function Count_by_uid_get() {

		// 插件id
		$plugin_id = I('get.plugin_id', 0);

		// 获取总数
		$serv_pm = D('Common/CommonPm', 'Service');
		$count = $serv_pm->count_by_uid_pluginid($this->_login->user['m_uid'], $plugin_id);

		// 返回结果
		$this->_result = array('count' => $count);
		return true;
	}

	/**
	 * 根据用户id和应用id读取记录列表
	 * @return mixed
	 */
	public function List_by_msg_pid_get() {

		// 传入参数
		$params = I('request.');

		// 扩展参数
		$extend = array(
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 获取数据
		$serv_pm = D('Common/CommonPm', 'Service');
		$list = $serv_pm->list_by_msg_pid($params, $extend);

		// 数据格式化
		foreach ($list['data'] as &$_v) {
			$serv_pm->format($_v);
		}

		// 返回数据
		$this->_result = $list;
		return $list;
	}

	/**
	 * 根据记录id标记已/未读状态
	 * @return bool
	 */
	public function Update_isread_by_ids_post() {

		// 传入参数
		$params = I('request.');
		// 需要标记的消息id
		$pm_ids = (array)$params['ids'];
		// 状态
		$isread = $params['isread'];

		// 获取数据
		$serv_pm = D('Common/CommonPm', 'Service');
		if (!$serv_pm->update_isread_by_ids($pm_ids, $isread)) {
			$this->_set_error($serv_pm->get_errmsg(), $serv_pm->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 发送消息
	 * @return bool
	 */
	public function Add_post() {

		// 消息信息
		$pm = array();
		// 传入参数
		$params = I('request.');

		// 扩展参数
		$extend = array(
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 数据入库
		$serv_pm = D('Common/CommonPm', 'Service');
		if (!$serv_pm->add($pm, $params, $extend)) {
			$this->_set_error($serv_pm->get_errmsg(), $serv_pm->get_errcode());
			return false;
		}

		// 返回数据
		$this->_result = $pm;
		return true;
	}
}
