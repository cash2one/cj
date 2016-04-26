<?php
/**
 * AbstractController.class.php
 * @create-time: 2015-07-01
 *
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */
namespace File\Controller\Api;

use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	/**
	 * 获取插件配置
	 * @return bool
	 */
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('File');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());
		return true;
	}

	/**
	 * 权限判断
	 * @param int $f_id 分组id
	 * @param int $type 成员类型: 15=组长; 12=协作者; 8=浏览者; 4=其他
	 * @param int $m_uid 用户id
	 * @return mixed
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	protected function _get_permission($f_id, $type, $m_uid) {

		// 用户权限
		$serv_p = D('File/FilePermission', 'Service');
		return $serv_p->is_permission($f_id, $type, $m_uid);
	}

	/**
	 * 获取文件信息
	 * @param int $f_id 分组/文件夹/文件id
	 * @return mixed
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	protected function _info($f_id) {

		$serv_f = D('File/File', 'Service');
		return $serv_f->get($f_id);
	}

	/**
	 * 根据附件id获取附件详情
	 * @param int $at_id 附件id
	 * @return mixed
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	protected function _info_attachment($at_id) {

		$serv_a = D('Common/CommonAttachment', 'Service');
		return $serv_a->get($at_id);
	}

	/**
	 * 根据键值查找数组
	 * @param array $arr 待查找数组
	 * @param $key 键
	 * @param $val 值
	 * @return array
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	protected function _seekarr($arr = array (), $key, $val) {

		$res = array ();
		$str = json_encode($arr);
		preg_match_all("/\{[^\{]*\"".$key."\"\:\"".$val."\"[^\}]*\}/", $str, $m);

		// 键值匹配
		if ($m && $m[0]) {
			foreach ($m[0] as $val) {
				$res = json_decode($val, true);
			}
		}

		return $res;
	}
}
