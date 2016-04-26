<?php
/**
 * voa_c_admincp_manage_member_position
 * User: luckwang
 * Date: 15/5/8
 * Time: 下午3:47
 */

class voa_c_admincp_manage_member_position extends voa_c_admincp_manage_member_base{

    protected $_positions = null;

    protected function _before_action($action) {

        if (!parent::_before_action($action)) {
            return false;
        }

        if ($this->_positions === null) {
            $this->_positions = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
        }

        return true;
    }

    public function execute() {
        $act = $this->request->get('act');
        switch ($act) {
            case 'delete':
                $this->__delete();
                break;
            case 'add':
                $this->__add();
                break;
            case 'save':
                $this->__save();
                break;
            default:
                if ($this->request->get_method() == 'POST') {
                    $this->__save();
                }
                $this->__list();
                break;
        }
    }

    /**
     * 职务列表
     */
    private function __list() {
        $positions = array();
        $this->__get_positions($positions, 0, 1, '0');

        $this->view->set('member_positions_url', $this->cpurl($this->_module, $this->_operation, 'positions', $this->_module_plugin_id));
        $this->view->set('positions', $positions);
        $this->output('manage/member/position');
    }

    /**
     * 保存职务列表
     */
    private function __save() {
        $names = $this->request->get('name');

        $uda_mp = &uda::factory('voa_uda_frontend_member_position');
        $uda_mp->position_save($names);

        $this->_positions = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
    }


    /**
     * 添加职务
     * @return bool
     */
    private function __add() {
        $parent_id = $this->request->get('parent_id');
        $parent_id = rintval($parent_id);

        if (empty($this->_positions[$parent_id])) {
            $this->_json_message(1000042, '职务不存在');
            return true;
        }

        $parent = $this->_positions[$parent_id];
        $data['mp_name'] = $parent['mp_name'] . '-子级'.rand(1,1000);
        $data['mp_parent_id'] = $parent_id;

        $result = array();
        $uda_mp = &uda::factory('voa_uda_frontend_member_position');

        if ($uda_mp->position_add($result, $data)) {
            $this->_json_message($uda_mp->errcode, $uda_mp->errmsg, $result);
        } else {
            $this->_json_message($uda_mp->errcode, $uda_mp->errmsg);
        }
    }


    /**
     * 删除职务
     */
    private function __delete() {
        $id = $this->request->get('id');

        $uda_mp = &uda::factory('voa_uda_frontend_member_position');
        $uda_mp->position_delete($id);

        $this->_json_message($uda_mp->errcode, $uda_mp->errmsg);
    }


    /**
     * 获取所有职务
     * @param $positions
     * @param int $parent_id
     * @param int $layer
     */
    function __get_positions(&$positions, $parent_id = 0, $layer = 1, $parent_ids = '') {
        //遍历所有职务，获取$parent_id下的所有职务
        foreach ($this->_positions as $key=>$position) {
            if ($position['mp_parent_id'] == $parent_id) {
                $temp['id'] = $position['mp_id'];
                $temp['name'] = $position['mp_name'];
                $temp['parent_id'] = $position['mp_parent_id'];
                $temp['parent_ids'] = $parent_ids . '_' . $position['mp_id'] ;
                $temp['layer'] = $layer;
                $temp['space'] = '';
                $c = ($layer - 1) * 4;
                for ($i = 0; $i < $c; $i++) {
                    $temp['space'] .= '<span class="space"></span>';
                }
                $positions[$key] = $temp;
                unset($this->_positions[$key]);

                $this->__get_positions($positions, $temp['id'], $layer + 1, $parent_ids . '_' . $temp['id']);
            }
        }
    }

}
