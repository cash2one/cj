<?php
/**
 * Created by PhpStorm.
 * User: Hu Sendong
 * Email: husendong@vchangyi.com
 */

namespace Sales\Controller\Apicp;

class SalesTrajectoryController extends AbstractController {


	/**
	 * 新增轨迹
	 * @return bool
	 * $author chen
	 */
	public function Add_track_post() {

		// 轨迹
		$trajectory = array();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array(
			'uid' => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);

		// 如果新增操作失败
		$serv_sb = D('Sales/SalesTrajectory', 'Service');
		if (!$serv_sb->add_trajectory($trajectory, $params, $extend)) {
			E($serv_sb->get_errcode() . ':' . $serv_sb->get_errmsg());
			return false;
		}

		$this->_result = $trajectory['st_id'];
		return true;
	}

    /**
     * 编辑轨迹
     * @param int $st_id 轨迹ID
     * @param int $sc_id 客户ID
     * @param String $st_content 工作日报
     * @param String $st_address 地址
     * @param int $st_type 客户状态
     * @param array $at_ids 附件
     * $author: husendong@vchangyi.com
     */
    public function Edit_trajectory_post() {

        // 编辑轨迹信息
        $trajectory = array();
        // 用户提交的参数
        $params = I('request.');

        // 非用户提交的扩展参数
        $extend = array(
            'uid' => $this->_login->user['m_uid'],
            'username' => $this->_login->user['m_username']
        );

        // 如果编辑操作失败
        $serv_trajectory = D('Sales/SalesTrajectory', 'Service');

        if (!$serv_trajectory->edit_trajectory($params, $extend)) {
            E($serv_trajectory->get_errcode() . ':' . $serv_trajectory->get_errmsg());
            return false;
        }

        $this->_result = $trajectory;
        return true;
    }

    /**
     * 轨迹列表查询
     * $author: husendong@vchangyi.com
     */
    public function List_trajectory_get() {
        // 每页条数
        $limit = (int)I('get.limit');
        $page = I('get.page');
        // 判断每页条数是否正确 ,如果不合法赋予系统默认值
        if ($limit < cfg('perpage_min') || $limit > cfg('perpage_max')) {
            $limit = $this->_plugin->setting['perpage'];
        }

        list($start, $limit, $page) = page_limit($page, $limit);
        // 分页参数
        $page_option = array(
            $start,
            $limit
        );

        // 用户提交的参数
        $params = I('request.');
        $serv_sc = D('Sales/SalesTrajectory', 'Service');
        $trajectorylist = array();

        // 获取轨迹列表
        if (!$serv_sc->list_trajectory_get($trajectorylist, $params, $page_option)) {
            E($serv_sc->get_errcode() . ':' . $serv_sc->get_errmsg());
            return false;
        }

        $this->_result = $trajectorylist;
        return true;
    }

}