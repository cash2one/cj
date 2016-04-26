<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace File\Controller\Frontend;
use Common\Common\Plugin;

class AbstractController extends \Common\Controller\Frontend\AbstractController {

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
