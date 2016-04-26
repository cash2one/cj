<?php
/**
 * AbstractSettingService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;
use Common\Common\Cache;

abstract class AbstractSettingService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 检查配置数据类型
	 * @param mixed $val 配置数据
	 * @param int $type 类型, 0: 非数组; 1: 数组
	 * @return boolean
	 */
	public function check_type(&$val, &$type) {

		$type = $this->_d->get_type_normal();
		// 如果是数组
		if (is_array($val)) {
			$type = $this->_d->get_type_array();
			$val = serialize($val);
		} elseif (FALSE !== unserialize($val)) { // 如果字串可序列化
			$type = $this->_d->get_type_array();
		}

		return true;
	}

	// 读取所有
	public function list_kv() {

		// 获取字段前缀
		$prefield = $this->get_prefield();
		// 读取配置
		$list = $this->_d->list_all();

		// 重新整合, 改成 key-value 键值对
		$sets = array();
		foreach ($list as $_v) {
			if ($this->_d->get_type_array() == $_v[$prefield.'type']) {
				$sets[$_v[$prefield.'key']] = unserialize($_v[$prefield.'value']);
			} else {
				$sets[$_v[$prefield.'key']] = $_v[$prefield.'value'];
			}
		}

		// 更新 pluginid, agentid
		$this->_check_pluginid_agentid($sets);

		return $sets;
	}

	/**
	 * 更新配置信息
	 * @param array $data 数据数组
	 * @param boolean $force 是否强制更新(插入)
	 * @return boolean
	 */
	public function update_kv($data, $force = false) {

		// 获取字段前缀
		$prefield = $this->get_prefield();

		$keys = array_keys($data);
		// 先读取配置信息
		$list = $this->_d->list_by_pks($keys);
		// 更新配置信息
		foreach ($list as $_v) {
			// 获取配置数据
			$val = $data[$_v[$prefield.'key']];
			$type = $this->_d->get_type_normal();
			// 检查配置数据类型
			$this->check_type($val, $type);
			// 更新数据
			$this->_d->update($_v[$prefield.'key'], array(
				"{$prefield}value" => $val,
				"{$prefield}type" => $type
			));

			// 剔除已更新的
			unset($data[$_v[$prefield.'key']]);
		}

		// 是否强制插入
		if ($force) {
			return true;
		}

		// 遍历所有待入库的配置(特殊情况)
		foreach ($data as $_k => $_v) {
			$type = 0;
			// 检查配置数据类型
			$this->check_type($_v, $type);
			// 更新记录
			$this->_d->insert(array(
				$prefield.'key' => $_k,
				$prefield.'value' => $_v,
				$prefield.'type' => $type
			));
		}

		return true;
	}

	/**
	 * 更新插件信息
	 * @param array $sets 配置信息
	 */
	protected function _check_pluginid_agentid(&$sets) {

		// 获取插件列表
		$cache = &Cache::instance();
		$plugins = $cache->get('Common.plugin');

		// 获取模块名称
		$module_name = rstrtolower(MODULE_NAME);
		$cur_plugin = array();
		// 获取当前应用的 pluginid, agentid
		foreach ($plugins as $_p) {
			// 找到匹配的插件
			if ($module_name == rstrtolower($_p['cp_identifier'])) {
				$cur_plugin = $_p;
				break;
			}
		}

		// 如果插件信息为空, 则
		if (empty($cur_plugin)) {
			return false;
		}

		$update = array();
		// 更新插件id
		if ($sets['pluginid'] != $cur_plugin['cp_pluginid']) {
			$update['pluginid'] = $cur_plugin['cp_pluginid'];
			$sets['pluginid'] = $cur_plugin['cp_pluginid'];
		}

		// 更新应用id
		if ($sets['agentid'] != $cur_plugin['cp_agentid']) {
			$update['agentid'] = $cur_plugin['cp_agentid'];
			$sets['agentid'] = $cur_plugin['cp_agentid'];
		}

		// 更新配置表数据
		if (!empty($update)) {
			$this->update_kv($update);
		}

		return true;
	}

}
