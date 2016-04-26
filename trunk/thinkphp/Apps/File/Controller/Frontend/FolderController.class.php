<?php
namespace File\Controller\Frontend;
use File\Controller\Api;
use Common\Common\Cache;
use Com;

class FolderController extends AbstractController {

	/**
	 * 文件列表
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function Folder_info() {

		// 获取参数
		$params = I('request.');
		$f_id = (int)$params['f_id'];
		// 获取分页数据
		$limit = (int)I('request.limit');
		$page = (int)I('request.page');
		// 判断当前页数为空,取第1页
		if (empty($page)) {
			$page = cfg('PAGE_MINSIZE');
		}
		// 调用API
		$folder_info = R('Api/Folder/Folder_info_get?f_id=2');
		$total = (int)$folder_info['total'];
		$serv_folder = D('File/Folder', 'Service');
		// 获取当前打开文件/文件夹的信息
		$info = $serv_folder->get_by_id($f_id);
		$filename = $info['f_name'];
		$group_id = $info['group_id'];
		$condition = $params['condition'];

		// 判断当前是否为分组
		if (\File\Model\FileModel::F_GROUP == $info['f_level']) {
			$forder_parents = $info['f_name'];
		} else {
			$f_parent_id = $info['f_parent_id'];
			// 根据父级ID查出所有上级文件夹
			$arr_names = $serv_folder->fetch_by_f_parent_id($f_parent_id);
			$forder_parents = implode('>',$arr_names).'>'.$filename;
			// 路径文字过长，只显示后面一部分,处理截取乱码
			if(strlen($forder_parents) > 60){
				$forder_parents = mb_substr($forder_parents,-56);
				$forder_parents = '...'.mb_substr($forder_parents,0,50, 'utf-8');
			}
		}


		// 输出模板变量
		$this->assign('f_id', $f_id);
		$this->assign('forder_parents', $forder_parents);
		// 列表API
		$this->assign('listurl', U('/File/Api/Folder/Folder_info_get'));
		// 搜索API
		$this->assign('searchurl', U('/File/Api/File/File_search_post'));
		// 加载Public文件路径
		$this->assign('static_path', cfg('static_path'));
		// 文件类型常量
		$this->assign('ftype',array(\File\Model\FileModel::F_FOLDER,\File\Model\FileModel::F_FILE));
		// 传文件详情URL
		$this->assign('fileurl', U('/File/Frontend/File/Detail'));
		// 传文件夹详情URL
		$this->assign('foderurl', U('/File/Frontend/Folder/Folder_info'));
		$this->assign('group_id', $group_id);
		$this->assign('condition', $condition);
		$this->assign('limit', $limit);
		$this->assign('page', $page);
		$this->assign('total', $total);
		$this->_output("Frontend/Folder/folder_info");
	}
}
