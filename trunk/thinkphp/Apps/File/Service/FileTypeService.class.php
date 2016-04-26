<?php
/**
 * FileTypeService.class.php
 * $author$
 */
namespace File\Service;

class FileTypeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("File/FileType");
	}

	/**
	 * 读取所有文件类型信息
	 * @param null $page_option 分页
	 * @param array $order_option 排序
	 * @return array
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function list_all($page_option = null, $order_option = array ()) {

		$list = parent::list_all($page_option, $order_option);
		return array_combine_by_key($list, 't_id');
	}
}
