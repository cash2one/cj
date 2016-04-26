<?php
namespace File\Controller\Frontend;

use File\Controller\Api;
use Common\Common\Cache;
use Com;

class GroupController extends AbstractController {

	/**
	 * 文件分组列表
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function Group_list() {

		$group_info = R('Api/Group/Group_list_get');
    	$total = (int)$group_info['total'];
		// 分页信息
		$limit = I('get.limit');
		$page = I('get.page');
		// 判断当前页数为空,取第1页
		if (empty($page)) {
			$page = cfg('PAGE_MINSIZE');
		}

		$group_id = (int)$group_info['data'][0]['group_id'];
		// 如果分组数只有一个，直接跳转到文件详情
		if(1 == $total){
			$this->assign('location','YES');
			$this->assign('f_url', U('/File/Frontend/Folder/Folder_info?f_id='.$group_id));
		}else{
			$this->assign('listurl', U('/File/Api/Group/Group_list_get'));
		}

		// 传URL
		$this->assign('folderurl', U('/File/Frontend/Folder/Folder_info'));
		// 加载Public文件路径
		$this->assign('static_path', cfg('static_path'));
		$this->assign('limit', $limit);
		$this->assign('page', $page);

		$this->_output("Frontend/Group/group_list");
	}
}
