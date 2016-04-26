<?php
/**
 * FileController.class.php
 * $author$
 */
namespace File\Controller\Frontend;

class FileController extends AbstractController {

	/**
	 * 文件详情
	 */
	public function Detail() {

		// 文件id
		$f_id = I('get.file_id');
		$group_id = I('get.group_id');
		$download = I('get.download');
		// 初始化下载参数
		$this->assign('down_load', "NO");
		// 文件详情接口
		$this->assign('listurl', U('/File/Api/File/File_info?file_id='.$f_id));
		// 文件详情
		$this->assign('fileurl', U('/File/Frontend/File/Detail?group_id='.$group_id.'&file_id='.$f_id));
		// 下载链接
		$this->assign('downdurl', U('/File/Frontend/File/Detail?download=1&group_id='.$group_id.'&file_id='.$f_id));
		// 公共文件路径
		$this->assign('static_path', cfg('static_path'));
		// 程序执行时间
		$this->assign('execute_time', cfg('max_execute_time'));
		// 调用文件评论列表接口，获取文件评论总数
		$comment_list = R('Api/Comment/Comment_list_get', array ($this->_plugin->get_pluginid()));
		$this->assign('comment_count', $comment_list['total']);
		// 文件评论列表
		$this->assign('commenturl', U('/File/Frontend/Comment/Comment?file_id='.$f_id));

		if ($download == 1) {
			// 更改下载参数
			$this->assign('down_load', "YES");
			// 获取下载路劲及下载文件大小
			$attach_info = R('Api/File/File_batch_download/');
			// 操作文件
			$serv_f = D('File/File', 'Service');
			// 获取文件大小
			$file_size = $serv_f->getRealSize($attach_info['attachment_size']);
			// 文件路径
			$file_url = iconv("UTF-8", "GBK", $serv_f->strreplace($attach_info['attachment_url']));
			// 文件名称
			$file_name = iconv("UTF-8", "GBK", substr($attach_info['attachment_url'], strrpos($attach_info['attachment_url'], '/') + 1));
			// 将上述获取的值返回到前台
			$this->assign('file_size', $file_size);
			$this->assign('file_url', $file_url);
			$this->assign('file_name', "/".$file_name);
		}

		// 输出模板
		$this->_output("Frontend/File/detail");
	}
}
