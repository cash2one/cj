<?php
/**
 * FileService.class.php
 * @create-time: 2015-07-01
 */
namespace File\Service;

class FileService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("File/File");
	}

	/**
	 * 根据文件夹/文件/创建人名称搜索
	 * @param string $name 文件夹或文件名称/创建人名称
	 * @param int $group_id 分组id
	 * @param array $order_option 排序
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function list_by_name_gid($name, $group_id, $order_option) {

		// 条件非空验证
		if (empty($name)) {
			$this->_set_error('_ERR_CONDITION_NOT_NULL');
			return false;
		}

		// 条件长度验证
		if (cfg('file_name_len') < mb_strlen($name, 'utf8')) {
			$this->_set_error('_ERR_NAME_LENGTH_ERROR');
			return false;
		}

		// 分组id不存在
		/*if (!$this->_get_by_id($group_id)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}*/

		return $this->_d->list_by_name_gid($name, $group_id, $order_option);
	}

	/**
	 * 上传附件后，添加文件信息
	 * @param array $file 上传文件信息
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function add_file(&$file, $params, $extend = array ()) {

		// 获取参数
		$folder_id = (int)$params['folder_id'];
		$at_id = (int)$params['at_id'];
		$filetype_id = 0;

		// 所在文件夹id不能为空，附件id非空验证
		if (empty($folder_id) && empty($at_id)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		if(!empty($params['filetype_id'])){
			$filetype_id = (int)$params['filetype_id'];
		}

		// 所在文件夹信息
		$finfo = $this->_get_by_id($folder_id);

		// 所在文件夹id有效性验证
		if (!$finfo) {
			$this->_set_error('_ERR_PARENTID_IS_NOT_EXIST');
			return false;
		}

		//记录不是文件夹或者分组
		if (!in_array($finfo['f_level'], array ($this->_d->get_f_group(), $this->_d->get_f_folder()))) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 根据附件id，取附件信息
		$serv_a = D('Common/CommonAttachment', 'Service');

		// 判断附件id是否有效
		if (!$att_info = $serv_a->get($at_id)) {
			$this->_set_error('_ERR_PARENTID_IS_NOT_EXIST');
			return false;
		}

		// 文件信息
		$file = array (
			'group_id' => $finfo['group_id'],
			'f_name' => $att_info['at_filename'],
			'f_parent_id' => $folder_id,
			'at_id' => $at_id,
			't_id' => $filetype_id,
			'm_uid' => $extend['m_uid'],
			'm_username' => $extend['m_username'],
			'f_level' => $this->_d->get_f_file(),
			'f_status' => $this->_d->get_st_create(),
			'f_created' => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($file)) {
			E(L('_ERR_INSERT_ERROR'));
			return false;
		}

		// 拼接返回数据
		$file['f_id'] = $id;
		return true;
	}

	/**
	 * 创建文件夹
	 * @param string $url 文件当前地址
	 * @return null
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function create_base_url($url) {

		//判断目录是否存在，若存在则删除
		if (file_exists($url)) {
			$this->deldir($url);
		}

		// 创建基础目录
		rmkdir($url, 0777);

		// 删除框架自动添加的文件index.html
		if (is_file($url."/index.html")) {
			unlink($url."/index.html");
		}
	}

	/**
	 * 创建文件夹
	 * @param string $url 文件当前地址
	 * @param string $folder 文件夹名
	 * @return string
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function create_folder($url, $folder) {

		$folder = iconv("UTF-8", "GBK", $folder);
		$curtempdir = $url."/".$folder;

		// 判断当前目录是否存在，若不存在则创建目录
		if (!file_exists($curtempdir)) {
			mkdir($curtempdir, 0777);
		}

		// 删除框架自动添加的文件index.html
		if (is_file($curtempdir."/index.html")) {
			unlink($curtempdir."/index.html");
		}

		return $curtempdir;
	}

	/**
	 * 复制文件
	 * @param string $cur_tempdir 文件目标路径
	 * @param string $old_url 文件源地址
	 * @param string $attach_name 文件名称
	 * @return string
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function copy_file($cur_tempdir, $old_url, $attach_name) {

		$attach_name = iconv("UTF-8", "GBK", $attach_name);
		$file = $cur_tempdir."/".$attach_name;

		return copy($old_url, $file);
	}

	/**
	 * 获取分组下文件和文件夹列表
	 * @param int $fid 分组id
	 * @return array
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function list_file($fid) {

		// 文件夹
		$f_id = (int)$fid;
		return $this->_d->list_file($f_id);
	}

	/**
	 * 获取当前文件夹下文件或文件夹
	 * @param array $file_list 文件信息
	 * @param int  $fid 当前文件夹id
	 * @return array
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function list_sub_file($file_list, $fid) {

		// 文件夹id
		$f_id = (int)$fid;

		// 遍历文件列表
		foreach ($file_list as $v) {

			// 判断是否为子文件
			if ($v['f_parent_id'] == $f_id) {
				$data[] = $v;
			}

		}

		return $data;
	}

	/**
	 * 获取当前文件夹下文件或文件夹
	 * @param string $dir 目录
	 * @return bool
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function deldir($dir) {

		//先删除目录下的文件：
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$fullpath = $dir."/".$file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					$this->deldir($fullpath);
				}
			}
		}

		closedir($dh);
		// 删除当前文件夹：
		if (!rmdir($dir)) {
			E(L('_ERR_DELETE_ERROR'));
			return false;
		}

	}

	/**
	 * 获取当前目录大小
	 * @param string $dir 目录
	 * @return int
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function getDirSize($dir)
	{
		$sizeResult=0;
		$handle = opendir($dir);
		while (false!==($FolderOrFile = readdir($handle)))
		{
			if($FolderOrFile != "." && $FolderOrFile != "..")
			{
				if(is_dir("$dir/$FolderOrFile"))
				{
					$sizeResult += $this->getDirSize("$dir/$FolderOrFile");
				}
				else
				{
					$sizeResult += filesize("$dir/$FolderOrFile");
				}
			}
		}
		closedir($handle);
		return $sizeResult;
	}
	/**
	 * 获取当前目录大小
	 * @param int $size 目录文件大小
	 * @return string
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function getRealSize($size) {

		$kb = 1024;         // Kilobyte
		$mb = 1024 * $kb;   // Megabyte
		$gb = 1024 * $mb;   // Gigabyte
		$tb = 1024 * $gb;   // Terabyte
		if ($size < $kb) {
			return $size." B";
		} else {
			if ($size < $mb) {
				return round($size / $kb, 2)." KB";
			} else {
				if ($size < $gb) {
					return round($size / $mb, 2)." MB";
				} else {
					if ($size < $tb) {
						return round($size / $gb, 2)." GB";
					} else {
						return round($size / $tb, 2)." TB";
					}
				}
			}
		}
	}

	/**
	 * 替换路径中部分格式
	 * @param string $str 目录文件大小
	 * @return string
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function strreplace($str){
		$str = str_replace('//', '/', $str);
		$str = str_replace('\\', '/', $str);
		$str = str_replace('\\\\', '/', $str);
		return trim($str);
	}
}
