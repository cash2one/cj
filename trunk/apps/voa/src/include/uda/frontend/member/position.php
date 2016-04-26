<?php
/**
 * Created by PhpStorm.
 * User: luckwang
 * Date: 15/5/12
 * Time: 上午10:55
 */

class voa_uda_frontend_member_position extends voa_uda_frontend_member_base {

    protected $_positions = null;

    public function __construct() {
        parent::__construct();

        if ($this->_positions === null) {
            $this->_positions = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
        }
    }

    /**
     * 保存职务列表信息
     * @param $positions array(id => name, id=>name, id=>name)
     * @return bool
     */
    public function position_save($positions) {
        if (empty($positions) ||
            !is_array($positions)) {
            return false;
        }
        $update = array();
        //过滤掉没有更新的职务
        foreach ($positions as $id => $position) {
            if (!empty($this->_positions[$id])) {
                if ($this->_positions[$id]['mp_name'] != $position) {
                    $update[$id] = $position;
                }
            }
        }

        $serv_mp = &service::factory('voa_s_oa_member_position');
        try {
            $serv_mp->begin();

            foreach ($update as $id => $name) {
                $serv_mp->update($id, array('mp_name' => $name));
            }
            $serv_mp->commit();
            $this->update_cache();
            return true;

        } catch(Exception $e) {
            $serv_mp->rollback();
            $this->errcode = 1002;
            $this->errmsg = '更新失败';
            logger::error($e);
            return false;
        }

    }

    /**
     * 删除职务
     * @param $id 职务id
     * @return bool
     */
    public function position_delete($id) {
        $id = rintval($id);
        if (empty($this->_positions[$id])) {
            return $this->set_errmsg(voa_errcode_oa_member::POSITION_NOT_EXISTS);
        }

		if ($this->_positions[$id]['mp_parent_id'] == 0) {
			$this->errcode = 1003;
			$this->errmsg = '不能删除顶级职务';
			return false;
		}

        if ($this->has_sub($id)) {
            $this->errcode = 1001;
            $this->errmsg = '不能删除有下级职务的职务';
            return false;
        }

        $serv_mp = &service::factory('voa_s_oa_member_position');
        $serv_md = &service::factory('voa_s_oa_member_department');
        try {

            $serv_mp->begin();
            $serv_mp->delete($id);

            $serv_md->update_by_conditions(array('mp_id' => 0), array('mp_id' => $id));

            $serv_mp->commit();

            unset($this->_positions[$id]);
            $this->update_cache();

        } catch (Exception $e) {
            $serv_mp->rollback();
            $this->errcode = 1002;
            $this->errmsg = '删除失败';
            logger::error($e);
            return false;
        }

        $this->errcode = 0;
        $this->errmsg = '删除成功';
        return true;
    }


    /**
     * 添加职务
     * @param $result 引用返回结果
     * @param $data 新增数据
     * @return bool
     */
    public function position_add(&$result, $data) {
        //判断父级id
        if (!empty($data['mp_parent_id'])) {
            if (empty($this->_positions[$data['mp_parent_id']])) {
                return $this->set_errmsg(voa_errcode_oa_member::POSITION_PARENT_ID_NOT_EXISTS);
            }
        } else{
            return $this->set_errmsg(voa_errcode_oa_member::POSITION_PARENT_ID_NOT_EXISTS);
        }
        //判断职务名称
        if ($data['mp_name'] != rhtmlspecialchars($data['mp_name'])) {
            return $this->set_errmsg(voa_errcode_oa_member::POSITION_NAME_ERROR);
        }

        $serv_mp = &service::factory('voa_s_oa_member_position');
        try{

            $result = $serv_mp->insert($data);
            $this->update_cache();
            return true;

        } catch(Exception $e) {
            $this->errcode = 2001;
            $this->errmsg = '删除失败';
            logger::error($e);
            return false;
        }
    }

    /**
     * 是否包含子级
     * @param $id 职务id
     * @return bool
     */
    public function has_sub($id) {
        if (empty($this->_positions[$id])) {
            return false;
        }

        foreach ($this->_positions as $p) {
            if ($p['mp_parent_id'] == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取子级职务id
     * @param $id
     * @return array
     */
    public function get_sub_position_ids($id) {

        if (empty($this->_positions[$id])) {
            return array();
        }

        $ids = array();
        foreach ($this->_positions as $p) {
            if ($p['mp_parent_id'] == $id) {
                array_push($ids, $p['mp_id']);
                $ids = array_merge($ids, $this->get_sub_position_ids($p['mp_id']));
            }
        }

        return $ids;
    }

    /**
     * 更新缓存
     */
    public function update_cache() {
        voa_h_cache::get_instance()->get('plugin.member.positions', 'oa', true);
    }
}
