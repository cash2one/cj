<?php
/**
 * GuestbookSettingService.class.php
 * $author$
 */
namespace Askfor\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class AskforSettingService extends AbstractSettingService {

	// 插件名称
	const PLUGIN_NAME = 'askfor';

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Askfor/AskforSetting');
	}

	/**
	 * 获取设置表里的所有数据
	 *
	 * @return mixed
	 */
	public function list_all_setting() {

		$list = array();
		foreach ($this->_d->list_all() as $k => $v) {
			$list[$v['afs_key']] = $v['afs_value'];
		}

		return $list;
	}

}
