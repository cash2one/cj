<?php
/**
 * base.php
 * UC / app版本信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_appversion_base extends voa_uda_uc_base {

	/** app 版本信息表操作 */
	public $serv_uc_appversion = null;

	/** app 客户端类型文字小写格式与真实格式映射关系 */
	public $app_client_type_map = array();

	public function __construct() {
		parent::__construct();

		if ($this->serv_uc_appversion === null) {

			$this->serv_uc_appversion = &service::factory('voa_s_uc_appversion');
			$this->app_client_type_map = array(
				rstrtolower(voa_d_uc_appversion::CLIENT_TYPE_IOS) => voa_d_uc_appversion::CLIENT_TYPE_IOS,
				rstrtolower(voa_d_uc_appversion::CLIENT_TYPE_ANDROID) => voa_d_uc_appversion::CLIENT_TYPE_ANDROID,
				rstrtolower(voa_d_uc_appversion::CLIENT_TYPE_WINPHONE) => voa_d_uc_appversion::CLIENT_TYPE_WINPHONE
			);

		}
	}

	/**
	 * 格式化输出一个版本信息数据
	 * @param array $version 原型数据
	 * @param array $output <strong style="color:red">(引用结果)</strong>输出后的数据
	 * @return boolean
	 */
	public function format($version = array(), &$output = array()) {
		$output = array(
			'id' => $version['ver_id'],
			'number' => $version['ver_number'],
			'clienttype' => $version['ver_clienttype'],
			'date' => $version['ver_date'],
			'forceupdate' => $version['ver_forceupdate'],
			'storeurl' => $version['ver_storeurl'],
			'download' => $version['ver_download'],
			'message' => $version['ver_message'],
		);
		unset($version);

		return true;
	}

	/**
	 * 格式化一组版本信息列表
	 * @param array $list 原型数据
	 * @param array $output <strong style="color:red">(引用结果)</strong>输出后的数据
	 * @return boolean
	 */
	public function format_list($list = array(), &$output = array()) {
		$output = array();
		foreach ($list as $version) {
			//$tmp = array();
			$this->format($version, $output[]);
			//$output[] = $tmp;
			//unset($tmp);
		}
		unset($list);

		return true;
	}

}
