<?php
/**
 * voa_uda_frontend_nvote_mem_option_add
 * 添加用户投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:55
 */

class voa_uda_frontend_nvote_mem_option_add extends voa_uda_frontend_nvote_mem_option_abstract {

    //投票选项service
    protected $_serv_option;
    //投票service
    protected $_serv_vote;
    //投票用户service
    protected $_serv_mem;

    protected $_nvotes = array();

    public function __construct()
    {
        parent::__construct();

        $this->_serv_option = &service::factory('voa_s_oa_nvote_option');
        $this->_serv_vote = &service::factory('voa_s_oa_nvote');
        $this->_serv_mem = &service::factory('voa_s_oa_nvote_mem');
    }

    /**
     * 获取投票信息
     * @param $id 主键id
     * @return mixed
     */
    protected function _get_nvote($id) {

        if (empty($this->_nvotes[$id])) {
            $this->_nvotes[$id] = $this->_serv_vote->get($id);
        }

        return $this->_nvotes[$id];
    }

    /**
     * 投票
     * @param $nvo_ids 选项id集合或单个id"1"，集合array(1,2,3)
     * @param $m_uid  用户id
     * @return bool
     */
    public function vote($nvo_ids, $m_uid) {
        //验证并获取选项数据
        $mem_options = array();
        if (!$this->__val_vo_ids($nvo_ids, $mem_options)) {
            return false;
        }

        $m_uid = rintval($m_uid);
        if ($m_uid < 1) {
            $this->errmsg('303', '获取用户信息失败');
            return false;
        }

        //判断选项是否属于同一个投票
        $mos_count = count($mem_options);
        for ($i = 0; $i < $mos_count; $i++) {
            for ($j = $i + 1; $j < $mos_count; $j++) {
                if (empty($mem_options[$i]['nvote_id']) ||
                        $mem_options[$i]['nvote_id'] != $mem_options[$j]['nvote_id']) {
                    $this->errmsg('305', '您所选的选项属于不同的投票');
                    return false;
                }
            }
            $mem_options[$i]['m_uid'] = $m_uid;
        }

        if (empty($mem_options)) {
            $this->errmsg('302', '不存在的投票选项');
            return false;
        }

        $current_mo = current($mem_options);
        $nvote_id = $current_mo['nvote_id'];

        $nvote = $this->_get_nvote($nvote_id);

        //判断用户是否已投过票
        $is_can_vote = $this->_serv_vote->is_can_vote($nvote_id, $m_uid);
        if ($is_can_vote == 2) {
            $this->errmsg('306', '用户没有受邀参加投票或已投过票');
            return false;
        }
        else if ($is_can_vote == 3) {
            //修改投票
            if ($nvote['is_repeat'] == voa_d_oa_nvote::REPEAT_YES) {
                return $this->_update_vote($m_uid, $nvote_id, $mem_options);
            }
            else {
                $this->errmsg('306', '用户没有受邀参加投票或已投过票');
                return false;
            }
        }

        //验证用户是否在投票用户中
        $mem = $this->_serv_mem->get_by_conds(array('nvote_id =?' => $nvote_id, 'm_uid =?' => $m_uid));

        //验证用户是否已经投过票
        if (!empty($mem) && $mem['is_nvote'] == voa_d_oa_nvote_mem::NVOTE_YES) {
            $this->errmsg('307', '不能重复投票');
            return false;
        }

        try {
            $this->_serv->begin();
            //添加用户选项数据
            $this->_serv->insert_multi($mem_options);
            //更新用户投票状态
            $this->_serv_mem->update_by_conds(array(
                                                'nvote_id =?' => $nvote_id,
                                                'm_uid =?' => $m_uid,
                                                'is_nvote =?' => voa_d_oa_nvote_mem::NVOTE_NO
                                                ),
                                                array('is_nvote' => voa_d_oa_nvote_mem::NVOTE_YES));
            //$this->_serv_mem->update($mem['id'], array('is_nvote' => voa_d_oa_nvote_mem::NVOTE_YES));

            //统计选项投票数
            foreach ($mem_options as $mem_option) {
                $this->_serv_option->update_by_conds($mem_option['nvote_option_id'], array('`nvotes`=`nvotes`+?' => 1));
            }
            //更新投票已投人数
            $this->_serv_vote->update_by_conds($nvote_id, array('`voted_mem_count`=`voted_mem_count`+?' => 1));

            $this->_serv->commit();
            return true;

        } catch (Exception $e) {

            logger::error(print_r($e, true));
            $this->_serv->rollback();
            /** 入库操作失败 */
            $this->errmsg(100, '操作失败');
            return false;
        }
    }

    /**
     * 更新投票选项
     * @param $m_uid 用户id
     * @param $nvote_id 投票id
     * @param $mem_options 投票新选项
     * @return bool
     */
    protected function _update_vote($m_uid, $nvote_id, $mem_options) {
        //获取用户已投的选项
        $o_m_options = $this->_serv->list_by_conds(array('nvote_id = ?' => $nvote_id, 'm_uid = ?' => $m_uid));

        $pk_mem_options = array();
        $delete = array();
        //替换主键为选项id
        foreach ($mem_options as $mo) {
            $pk_mem_options[$mo['nvote_option_id']] = $mo;
        }

        //遍历已投的选项
        foreach ($o_m_options as $mo) {
            //剔除没变化的选项
            if (isset($pk_mem_options[$mo['nvote_option_id']])) {
                unset($pk_mem_options[$mo['nvote_option_id']]);
                //剔除后，剩下则为需新增的选项
            } else {
                //需要删除的选项
                $delete[] = $mo;
            }
        }

        if (empty($pk_mem_options) &&
                empty($delete)) {
            $this->errmsg(309, '投票选项没有变更');
            return false;
        }

        try {
            $this->_serv->begin();
            if (!empty($pk_mem_options)) {
                //添加用户选项数据
                $this->_serv->insert_multi($pk_mem_options);
            }

            //统计选项投票数 +1
            foreach ($pk_mem_options as $mem_option) {
                $this->_serv_option->update_by_conds($mem_option['nvote_option_id'], array('`nvotes`=`nvotes`+?' => 1));
            }

            $delete_id = array();
            //统计选项投票数 -1
            foreach ($delete as $mem_option) {
                $this->_serv_option->update_by_conds($mem_option['nvote_option_id'], array('`nvotes`=`nvotes`-?' => 1));
                $delete_id[] = $mem_option['id'];
            }
            //删除投票用户选项
            if ($delete_id) {
                $this->_serv->delete_by_conds(array('id IN (?)' => $delete_id));
            }

            $this->_serv->commit();
            return true;

        } catch (Exception $e) {

            logger::error(print_r($e, true));
            $this->_serv->rollback();
            /** 入库操作失败 */
            $this->errmsg(100, '操作失败');
            return false;
        }
    }

    /**
     * 验证选项id
     * @param $nvo_ids 传入的选项id
     * @param $mem_options 输出用户选项
     * @return bool
     */
    private function __val_vo_ids ($nvo_ids, &$mem_options) {

        //集合
        if (is_array($nvo_ids)) {
            foreach ($nvo_ids as $vo_id) {
                $mem_option = array();
                if (!$this->__val_vote_option($vo_id, $mem_option)) {
                    return false;
                }
                $mem_options[] = $mem_option;
            }
        }
        //单个选项id
        elseif (is_numeric($nvo_ids)) {

            $mem_option = array();
            if (!$this->__val_vote_option($nvo_ids, $mem_option)) {
                return false;
            }
            $mem_options[] = $mem_option;

        } else {
            $this->errmsg('302', '不存在的投票选项');
            return false;
        }
        return true;
    }

    /**
     * 验证数据记录信息
     * @param $vo_id 投票选项id
     * @return bool
     */
    private function __val_vote_option($vo_id, &$mem_option) {

        $vo_id = rintval($vo_id);
        if ($vo_id < 1) {
            $this->errmsg('302', '不存在的投票选项');
            return false;
        }

        //判断表中是否存在
        $option = $this->_serv_option->get($vo_id);
        if (empty($option)) {
            $this->errmsg('302', '不存在的投票选项');
            return false;
        }

        //$nvote = $this->_serv_vote->get($option['nvote_id']);
        $nvote = $this->_get_nvote($option['nvote_id']);
        if ($nvote['end_time'] < startup_env::get('timestamp')) {
            $this->errmsg('304', '投票已结束');
            return false;
        }

        $mem_option['nvote_id'] = $option['nvote_id'];
        $mem_option['nvote_option_id'] = $option['id'];
        $mem_option['ip'] = controller_request::get_instance()->get_client_ip();
        return true;
    }

}
