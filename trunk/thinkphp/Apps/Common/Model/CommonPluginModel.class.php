<?php
/**
 * CommonPluginModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class CommonPluginModel extends AbstractModel {

	// 新应用（从未启用过）
	const AVAILABLE_NEW = 0;
	// 启用状态：等待开启
	const AVAILABLE_WAIT_OPEN = 1;
	// 启用状态：等待关闭
	const AVAILABLE_WAIT_CLOSE = 2;
	// 启用状态：等待删除
	const AVAILABLE_WAIT_DELETE = 3;
	// 启用状态：已启用
	const AVAILABLE_OPEN = 4;
	// 启用状态：已关闭
	const AVAILABLE_CLOSE = 5;
	// 启用状态：已删除
	const AVAILABLE_DELETE = 6;
	// 未开放的应用
	const AVAILABLE_NONE = 255;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cp_';
	}

	/**
	 * 根据唯一标识读取插件信息
	 * @param string $identifier 唯一标识
	 */
	public function get_by_identifier($identifier) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `cp_identifier`=? AND `cp_status`<?", array(
			$identifier, $this->get_st_delete()
		));
	}

}
