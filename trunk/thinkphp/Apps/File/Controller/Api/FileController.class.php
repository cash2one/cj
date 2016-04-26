<?php
/**
 * FileController.class.php
 * @create-time: 2015-07-01
 */
namespace File\Controller\Api;
use Org\Net\PHPZip;
use Org\Net\ReadDocument;

class FileController extends AbstractController {

	/**
	 * 文件详情
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function File_info_get() {

		// 文件id
		$f_id = I('request.file_id');

		// 文件详情
		$file_info = $this->_info($f_id);

		// 有效性验证
		if (!$file_info) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 判断权限
		if (!$this->_get_permission($file_info['group_id'], \File\Model\FilePermissionModel::MT_BROWSE, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 不是文件类型
		if (\File\Model\FileModel::F_FILE != $file_info['f_level']) {
			$this->_set_error('_ERR_IS_NOT_FILE');
			return false;
		}

		// 附件id
		$at_id = $file_info['at_id'];

		// 删除附件id
		unset($file_info['at_id']);

		// 根据附件id获取大小及路径
		$attachment_info = $this->_info_attachment($at_id);

		// 附件信息存在， 拼接附件信息
		if (!empty($attachment_info)) {
			$file_info['at_filesize'] = $attachment_info['at_filesize'];
			$file_info['at_attachment'] = '/attachment/read/'.$at_id;
		}

		// 截取文件名称
		$arr_name = explode(".",$attachment_info['at_filename']);

		// 文件为txt类型
		if (end($arr_name) == 'txt') {

			// 文件地址
			$url = cfg('HTTP_HOST').$file_info['at_attachment'];

			// 读取文件内容
			header("content-type:text/plain;charset=utf-8");
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_FILETIME, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$json_string = curl_exec($ch);
			curl_close($ch);
			$content = trim($json_string);

			$file_info['f_content'] = (string)$content;
		}
		// 文件为doc类型
		if (end($arr_name) == 'docx' || end($arr_name) == 'doc') {

			// 文件地址
//			$url = cfg('HTTP_HOST').$file_info['at_attachment'];
			// 实例化文档阅读类
//			$read = new ReadDocument();
			// 读文件内容
//			$content = $read->read($url,"application/msword");

			include_once $_SERVER['DOCUMENT_ROOT'].'/file_static/Sample_Header.php';

			// 构建路径
			$hostarr = explode('.', $_SERVER['HTTP_HOST']);
			$domain = rawurlencode(current($hostarr));
			$md5 = md5($domain);
			$document_root = $_SERVER['DOCUMENT_ROOT'];
			$root_path = substr($document_root, 0, strrpos($document_root, '/'));

			// 加载源文件
			$source = $root_path.cfg('STATICDIR').substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.$attachment_info['at_attachment'];
			$phpWord = \PhpOffice\PhpWord\IOFactory::load($source);

			// 保存为html文件
			$writers = array('HTML' => 'html');
			write($phpWord, basename($attachment_info['at_attachment'],".doc"), $writers);

			// html文件路径
			$sitedir = $root_path.cfg('STATICDIR').substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.date("Y").'/'.date("m").'/results/'.basename($attachment_info['at_attachment'],".doc").'.html';

			// 获取html内容并返回
			$contentd = file_get_contents($sitedir);
			$find = array("<body>","</body>");
			$content = str_replace($find,'',$contentd);
			$file_info['f_content'] = (string)$content;
		}

		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');
		$serv_fmt->file($file_info);

		// 返回结果
		$this->_result = $file_info;
		return $file_info;
	}

	/**
	 * 根据文件夹/文件/创建人名称搜索
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function File_search_post() {

		// 获取参数
		$params = I('request.');
		$name = (string)$params['condition'];
		$group_id = (int)$params['group_id'];

		// 分组id非空验证
		/*if (empty($group_id)) {
			$this->_set_error('_ERR_GROUP_ID_IS_NOT_EXIST');
			return false;
		}*/

		// 判断权限
		/*if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_BROWSE, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}*/

		// 根据搜索条件获取列表数据
		$serv_f = D('File/File', 'Service');
		$list_file = $serv_f->list_by_name_gid($name, $group_id, array ('f_id' => "DESC"));

		// 取附件id集合
		$att_ids = array ();
		foreach ($list_file as &$_v) {
			if (\File\Model\FileModel::F_FILE == $_v['f_level']) {
				array_push($att_ids, $_v['at_id']);
			}
		}

		// 附件列表
		$list_attachment = array ();
		if (!empty($att_ids)) {
			// 取附件列表
			$serv_a = D('Common/CommonAttachment', 'Service');
			$list_attachment = $serv_a->list_by_pks($att_ids);
		}

		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');

		// 文件类型，拼接附件信息
		foreach ($list_file as &$_v) {
			if (\File\Model\FileModel::F_FILE == $_v['f_level'] && !empty($list_attachment)) {

				// 根据键值取相应数据
				$att_info = $this->_seekarr($list_attachment, 'at_id', $_v['at_id']);

				// 拼接附件信息
				$_v['at_filesize'] = $att_info['at_filesize'];
				$_v['at_attachment'] = '/attachment/read/'.$_v['at_id'];
			}
			// 格式化
			$serv_fmt->file($_v);
		}
		unset($_v);

		// 返回数据
		$this->_result = $list_file;
		return true;
	}

	/**
	 * 新增文件信息
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function File_upload_post() {

		// 初始化文件夹信息
		$file = array ();

		// 获取参数
		$params = I('request.');

		// 上传附件，获取附件id
		$attachment_info = R('PubApi/Api/Attachment/Upload_post', array($this->_login->user['m_uid'],$this->_login->user['m_username']));
		if(empty($attachment_info)){
			$this->_set_error('_ERR_ATTACHMENT_IS_NOT_EXIST');
			return false;
		}

		$at_id = $attachment_info['at_id'];
		$params['at_id'] = $at_id;

		// 文件详情
		$file_info = $this->_info($params['folder_id']);

		// 有效性验证
		if (!$file_info) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 权限验证
		if (!$this->_get_permission($file_info['group_id'], \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 非用户提交的扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 数据入库
		$serv_f = D('File/File', 'Service');
		if (!$serv_f->add_file($file, $params, $extend)) {
			$this->_set_error($serv_f->get_errmsg(), $serv_f->get_errcode());
			return false;
		}

		// 附件信息存在， 拼接附件信息
		$file['at_filesize'] = $attachment_info['at_filesize'];
		$file['at_attachment'] = '/attachment/read/'.$at_id;

		// 父级id名称
		$f_info = $this->_info($file['f_parent_id']);
		$file['f_parent_name'] = $f_info['f_name'];

		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');
		$serv_fmt->file($file);

		// 返回数据
		$this->_result = $file;
		return true;
	}

	/**
	 * 文件批量下载
	 * @return String 附件地址
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function File_batch_download() {

		// 获取参数
		$params = I('request.');

		// 分组id
		$group_id = (int)$params['group_id'];

		// 下载ids
		$down_ids = !empty($params['down_ids'])?(array)$params['down_ids']:(array)$params['file_id'];

		// 非用户提交的扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);
		
		// 判断权限
		if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 页面传递的文件id不能为空
		if (empty($down_ids)) {
			$this->_set_error('_ERR_DOWNLOAD_FILE');
			return false;
		}

		// 设置内存
		ini_set('memory_limit', cfg('memory_limit'));

		// 设置执行时间
		set_time_limit(cfg('max_execute_time'));

		// 返回值数组
		$return_data = array ();

		// 设置下载临时目录
		$tempdir = APP_PATH."Runtime/Attach_temp/".$extend['m_uid'];

		// 拼接附件真实目录
		$hostarr = explode('.', $_SERVER['HTTP_HOST']);
		$domain = rawurlencode($hostarr[0]);
		$md5 = md5($domain);
		$document_root = $_SERVER['DOCUMENT_ROOT'];
		$root_path = substr($document_root, 0, strrpos($document_root, '/'));

		// 实例化压缩文件
		$zip = new PHPZip();

		// 操作文件
		$serv_f = D('File/File', 'Service');

		// 创建基础临时目录
		$serv_f->create_base_url($tempdir);

		// 获取当前分组下的文件及文件夹信息
		$file_list = $serv_f->list_file($group_id);

        // 返回全部附件信息
		$serv_a = D('Common/CommonAttachment', 'Service');
		$attachments = $serv_a->list_all();

		// 判断前端是否传一个id
		if (count($down_ids) == 1) {
			$file_inf = $serv_f->get($down_ids[0]);

			// 判断是否为文件
			if ($file_inf['f_level'] == \File\Model\FileModel::F_FILE) {

				// 附件信息
				$attachment_info = $this->_seekarr($attachments,'at_id', $file_inf['at_id']);

				// 文件路径
				$file_dir = $root_path.'/data/attachments/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.$attachment_info['at_attachment'];

				// 附件名称
				$attachment_name = $attachment_info['at_filename'];

				// 复制文件
				if ($serv_f->copy_file($tempdir, $file_dir, $attachment_name)) {

					// 返回数据
					$return_data['attachment_url'] = $tempdir."/".$attachment_name;
					$return_data['attachment_size'] =  $attachment_info['at_filesize'];

                    // 文件大小不能超过20M
					if($return_data['attachment_size']>cfg('UPLOAD_MAXSIZE')){
						$this->_set_error('_ERR_ATTACHMENT_IS_LARGE');
						return false;
					}

				}

			} else {

				if ($file_inf['f_level'] == \File\Model\FileModel::F_FOLDER) {// 判断为文件夹时

					// 创建文件夹
					$cur_tempdir = $serv_f->create_folder($tempdir, $file_inf['f_name']);
					$this->list_attach($file_list, $file_inf['f_id'], $cur_tempdir, $attachments);

					// 当前路径下文件大小
					$attachment_size=$serv_f->getDirSize($cur_tempdir);

					// 文件大小不能超过20M
					if($attachment_size>cfg('UPLOAD_MAXSIZE')){
						$this->_set_error('_ERR_ATTACHMENT_IS_LARGE');
						return false;
					}

					// 生成压缩包
					$zip->zipDir($cur_tempdir, $tempdir."/".$file_inf['f_name'].".zip");

					// 压缩文件名称
					$zip_name = iconv("UTF-8", "GBK", $file_inf['f_name']);

					// 返回数据
					$return_data['attachment_url'] = $tempdir."/".$file_inf['f_name'].".zip";

					// 压缩文件大小
					$return_data['attachment_size'] = filesize($tempdir."/".$zip_name.".zip");
				}

			}
		} else { // 判断前台传递多个id时

			$file_inf = $serv_f->list_by_pks($down_ids);

			// 遍历前端传递的列表信息
			foreach ($file_inf as $f) {

				// 记录文件夹信息
				if ($f['f_level'] == \File\Model\FileModel::F_FOLDER) {
					$folder[] = $f;
				} else {

					// 记录文件信息
					if ($f['f_level'] == \File\Model\FileModel::F_FILE) {
						$file[] = $f;
					}

				}
			}

			// 遍历文件夹
			foreach ($folder as $v) {

				// 创建文件夹并遍历文件夹下的目录
				$cur_tempdir = $serv_f->create_folder($tempdir, $v['f_name']);
				$this->list_attach($file_list, $v['f_id'], $cur_tempdir, $attachments);
			}

			// 将用户前台选择的文件复制到临时文件夹
			foreach ($file as $v) {

				// 附件信息
				$attachment_info = $this->_seekarr($attachments,'at_id',$v['at_id']);

				// 文件路径
				$file_list_dir = $root_path.'/data/attachments/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.$attachment_info['at_attachment'];

					// 复制
				$serv_f->copy_file($tempdir, $file_list_dir, $attachment_info['at_filename']);
			}

			// 当前路径下文件大小
			$attachment_size=$serv_f->getDirSize($tempdir);

			// 文件大小不能超过20M
			if($attachment_size>cfg('UPLOAD_MAXSIZE')){
				$this->_set_error('_ERR_ATTACHMENT_IS_LARGE');
				return false;
			}

			//打包并下载
			$zip->zipDir($tempdir, $tempdir."/"."file_".date('Ymd').".zip");

			// 返回数据
			$return_data['attachment_url'] = $tempdir."/"."file_".date('Ymd').".zip";
			// 压缩文件大小
			$return_data['attachment_size']=filesize($tempdir."/"."file_".date('Ymd').".zip");
		}

		$this->_result = $return_data;
		return $return_data;
	}

	/**
	 * 递归创建文件夹和文件的临时目录
	 * @param array $file_list 文件或文件夹列表
	 * @param int $fid 文件或文件夹id
	 * @param string $cur_tempdir 当前路径
	 * @param array $attachments 附件信息
	 * @return array
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */

	public function list_attach($file_list, $fid, $cur_tempdir, $attachments) {

		$f_id = (int)$fid;

		// 操作文件
		$serv_f = D('File/File', 'Service');

		// 有效性验证
		if (!$file_list) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 获取子文件或文件夹信息
		$attach_inf = $serv_f->list_sub_file($file_list, $f_id);

		// 遍历上述已获取信息列表
		foreach ($attach_inf as $f) {

			// 判断为文件时
			if ($f['f_level'] == \File\Model\FileModel::F_FILE) {

				// 附件信息
				$attachment_info = $this->_seekarr($attachments,'at_id', $f['at_id']);

				// 设置附件目录
				$hostarr = explode('.', $_SERVER['HTTP_HOST']);
				$domain = rawurlencode($hostarr[0]);
				$md5 = md5($domain);
				$document_root = $_SERVER['DOCUMENT_ROOT'];
				$root_path = substr($document_root, 0, strrpos($document_root, '/'));

				// 文件路径
				$file_list_dir = $root_path.'/data/attachments/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.$attachment_info['at_attachment'];
				$serv_f->copy_file($cur_tempdir, $file_list_dir, $attachment_info['at_filename']);
			} else {

				// 判断为文件夹时
				if ($f['f_level'] == \File\Model\FileModel::F_FOLDER) {

						// 创建文件夹
					$cur_tempdir = $serv_f->create_folder($cur_tempdir, $f['f_name']);

					// 递归调用
					$this->list_attach($file_list, $f['f_id'], $cur_tempdir, $attachments);
				}

			}
		}
		return true;
	}

}
