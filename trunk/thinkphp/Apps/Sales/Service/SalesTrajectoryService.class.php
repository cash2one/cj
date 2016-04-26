<?php
/**
 * SalesTrajectoryService.class.php
 * $author$
 */

namespace Sales\Service;

class SalesTrajectoryService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sales/SalesTrajectory");
	}

    /**
     * 轨迹列表查询
     * @param array $list_trajectory 轨迹列表
     * @param array $params 传入参数
     * @param array $page_option 分页参数
     * @return bool 是否查询成功 true 查询成功 false 查询失败
     * $author: husendong@vchangyi.com
     */
    public function list_trajectory_get(&$list_trajectory,$params, $page_option){

        $list_trajectory=$this->_d->list_trajectory_get($params, $page_option);
        return true;
    }

    /**
     * 编辑轨迹
     * @param array $params 传入参数
     * @return bool 是否编辑成功 true编辑成功 false编辑失败
     * @email: husendong@vchangyi.com
     */
    public function edit_trajectory($params){

        // 轨迹ID
        $st_id = (int)$params['st_id'];
        // 客户ID
        $sc_id = (int)$params['sc_id'];
        // 工作日报
        $st_content = (string)$params['st_content'];
        // 地址
        $st_address = (string)$params['st_address'];
        // 客户状态（当前客户状态）
        $st_type =  (int)$params['st_type'];
        // 附件
        $at_ids = (array)$params['at_ids'];

        // 根据轨迹ID获得轨迹详情
        // 轨迹ID不能为空
        if(empty($st_id)){
            $this->_set_error('_ERR_TRAJECTORY_ID_MESSAGE');
            return false;
        }

        // 客户ID不能为空
        if(empty($sc_id)){
            $this->_set_error('_ERR_TRAJECTORY_SCID_MESSAGE');
            return false;
        }

        // 工作日报不能为空
        if(empty($st_content)){
            $this->_set_error('_ERR_TRAJECTORY_CONTENT_MESSAGE');
            return false;
        }

        // 地址不能为空
        if(empty($st_address)){
            $this->_set_error('_ERR_TRAJECTORY_ADDRESS_MESSAGE');
            return false;
        }

        // 客户状态不能为空
        if(empty($st_type)){
            $this->_set_error('_ERR_TRAJECTORY_TYPE_MESSAGE');
            return false;
        }

        // 传入参数
        $trajectory = array(
            'sc_id'=>$sc_id,
            'st_content'=>$st_content,
            'st_address'=>$st_address,
            'st_type'=>$st_type,
            'at_ids'=>$at_ids
        );

        // 如果执行出错
        if (!$sc_id = $this->_d->edit_trajectory($st_id,$trajectory)) {
            $this->_set_error('_ERR_EDIT_TRAJECTORY_MESSAGE');
            return false;
        }

        return true;
    }

	/**
	 * 新增轨迹
	 * @param array $SalesTrajectory 轨迹
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool 是否添加轨迹成功 true 添加成功 false添加失败
	 */
	public function add_trajectory(&$SalesTrajectory, $params, $extend = array()) {

		// 销售人员ID
		$uid = (int)$extend['uid'];
		// 销售人员名称
		$username = (string)$extend['username'];
		// 客户ID
		$sc_id = (int)$params['sc_id'];
		// 客户状态
		$st_type = (int)$params['st_type'];
		// 工作描述（汇报）
		$st_content = (string)$params['st_content'];
		// 附件
		$at_ids = (array)$params['at_ids'];
		// 地址
		$st_address = (string)$params['st_address'];

		// 客户ID不能为空
		if (empty($sc_id)) {
			$this->_set_error('_ERR_CUSTOMER_MESSAGE');
			return false;
		}

		// 客户状态
		if (empty($st_type)) {
			$this->_set_error('_ERR_CUSTOMER_TYPE_MESSAGE');
			return false;
		}

		// 附件
		$at_ids = array_filter($at_ids);
		if (!empty($at_ids)) {
			$at_ids = implode(',', $at_ids);
		}

		// 轨迹
		$SalesTrajectory = array(
			'sc_id' => $sc_id,
			'm_uid' => $uid,
			'm_username' => $username,
			'st_content' => $st_content,
			'st_type' => $st_type,
			'at_ids' => $at_ids,
			'st_address' => $st_address,
			'sb_status' => $this->_d->get_st_create(),
			'sb_created' => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($SalesTrajectory)) {
			$this->_set_error('_ERR_TRAJECTORY_INSERT_ERROR');
			return false;
		}

		$business['st_id'] = $id;
		return true;
	}
}
