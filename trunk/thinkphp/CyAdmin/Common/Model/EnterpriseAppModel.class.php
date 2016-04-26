<?php
/**
 * EnterpriseProfileModel.class.php
 * $author$
 */

namespace Common\Model;

class EnterpriseAppModel extends AbstractModel {

	// 待创建
	const APP_STATUS_WAITING = 0;
	// 待删除
	const APP_STATUS_DELING = 1;
	// 待关闭
	const APP_STATUS_CLOSING = 2;
	// 已创建
	const APP_STATUS_OK = 3;
	// 已删除
	const APP_STATUS_DELETED = 4;
	// 已关闭
	const APP_STATUS_CLOSED = 5;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ea_';
	}

	/**
	 * 统计一天的新安装的应用
	 * @return array
	 */
	public function count_new_app() {

		$where = array(
			'ea_status < ?',
			'ea_created > ?',
			'ea_created < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime('yesterday'),
			rstrtotime('today'),
		);

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}

	public function list_app_status() {

		return array(
			self::APP_STATUS_WAITING, self::APP_STATUS_DELING,
			self::APP_STATUS_CLOSING, self::APP_STATUS_OK,
			self::APP_STATUS_DELETED, self::APP_STATUS_CLOSED
		);
	}

	// 获取状态: 待建立
	public function get_app_status_waiting() {

		return self::APP_STATUS_WAITING;
	}

	/**
	 * 切换别名到状态值
	 * @param int $appstatus 应用装置
	 * @param string $alias 状态别名
	 * @return boolean
	 */
	public function toggle_alias2appstatus(&$appstatus, $alias) {

		// 如果是正常状态值
		if (in_array($alias, $this->list_app_status())) {
			$appstatus = $alias;
			return true;
		}

		// 状态别名
		$alias2status = array(
			'open' => self::APP_STATUS_WAITING, // 设置为待开启
			'wait_open' => self::APP_STATUS_WAITING, // 设置为待开启
			'close' => self::APP_STATUS_CLOSING, // 设置为待关闭
			'wait_close' => self::APP_STATUS_CLOSING, // 设置为待关闭
			'delete' => self::APP_STATUS_DELING, // 设置为待删除
			'wait_delete' => self::APP_STATUS_DELING, // 设置为待删除
			'confirm_open' => self::APP_STATUS_OK, // 确定开启
			'confirm_close' => self::APP_STATUS_CLOSED, // 确定关闭
			'confirm_delete' => self::APP_STATUS_DELETED // 确定删除
		);

		// 判断别名是否存在
		if (isset($alias2status[$alias])) {
			$appstatus = $alias2status[$alias];
			return true;
		}

		return false;
	}
}
