<?php
/**
 * 缓存类
 */

class voa_h_cache extends mtc {

	/**
	 * get_instance
	 *
	 * @param  mixed $group 组名
	 * @return void
	 */
	public static function get_instance() {
		if (!self::$_instances) {
			self::$_instances = new self();
		}

		return self::$_instances;
	}

	/**
	 * 读取缓存
	 * @param string $key 缓存名称
	 * @param string $group 该缓存所属分组
	 * @param boolean $force_rw 是否强制读取并更新
	 */
	public function get($key, $group = null, $force_rw = false) {

		if ($force_rw) {
			parent::remove($key, $group);
		}

		return parent::get($key, $group, $force_rw);
	}

	/**
	 * 根据配置清理所有临时缓存
	 * @param string $group 所属组
	 */
	public function clear_all($group) {

		$uda = &uda::factory('voa_uda_frontend_base');
		$uda->update_cache();
		return true;
	}

	/**
	 * 获取缓存文件路径
	 * @param string $key
	 * @param string $group
	 */
	protected function _get_file_path($key, $group = null) {
		$kg = md5($key.$group);
		if(!isset($this->_cache_paths[$kg])) {
			$sitedir = voa_h_func::get_sitedir(startup_env::get('domain'));
			$this->_cache_paths[$kg] = $sitedir.'/'.$key.'.php';
		}

		return $this->_cache_paths[$kg];
	}

}
