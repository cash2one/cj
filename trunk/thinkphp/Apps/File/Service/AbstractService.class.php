<?php
/**
 * AbstractService.class.php
 * @create-time: 2015-07-01
 */
namespace File\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据主键取分组/文件夹/文件信息
	 * @param $f_id 分组/文件夹/文件id
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	protected function _get_by_id($f_id) {

		$file_d = D("File/File");
		return $file_d->get($f_id);
	}

	/**
	 * 根据指定字段返回相应值
	 * @param array $group_data 分组信息
	 * @param array 指定字段
	 * @return array
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function get_filelds($group_data, $fields = array ()) {

		// 返回数组初始化
		$f_data = array ();

		foreach ($fields as $v) {
			$f_data[$v] = $group_data[$v];
		}
		return $f_data;
	}

}
