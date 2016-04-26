<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:22
 */

namespace Campaigns\Model;

class CampaignsTypeModel extends AbstractModel {

    public function get_list() {
        return $this->list_all(null, array('order_sort' => 'ASC'));
    }

    public function save_type($post) {
        $this->start_trans();
        /**
         * 处理删除
         */
        //验证是否能够删除
        $this->_del_types($post['del_ids']);
        /**
         * 验证保存的数据
         */
        $add = array();
        $saves = array();
        $this->_validation_save_data($post, $add, $saves);

        /**
         * 处理更新
         */
        foreach ($saves as $save) {
            if ($this->_m->save($save) === false) {
                $this->rollback();
                E('_CAM_TYPE_SAVE_ERR');
                return false;
            }
        }
        /**
         * 处理新增
         */
        if($add){
            if ($this->_m->addAll($add) === false) {
                        $this->rollback();
                        E('_CAM_TYPE_ADD_ERR');
                        return false;
            }
        }

        /**
         * 验证数据库中的分类个数
         */
        if ($this->count() > 10) {
            $this->rollback();
            E('_CAM_TYPE_SUM_MAX_ERR');
        }
        $this->commit();
        return true;
    }

    /**
     * 删除分类
     */
    private function _del_types($type_ids) {
        //如果为空则不进行操作
        $type_ids = trim($type_ids, " \t\r\n");
        if ($type_ids == '') {
            return true;
        }
        //验证ids
        if (!preg_match('/^\d{1,}(,\d{1,}){0,}\d{0,}$/', $type_ids)) {
            //删除的id格式不正确
            E('_CAM_TYPE_DEL_IDS_ERR');
        }
        if ($this->_whether_del($type_ids)) {
            //执行删除
            if ($this->delete(explode(',', $type_ids)) === false) {
                //删除失败
                $this->rollback();
                E('_CAM_TYPE_DEL_DIE_ERR');
                return false;
            }
            return true;
        }
        return FALSE;
    }
    private function _whether_del($type_ids){
        //检测分下是否子菜单
        $cam_m=M('Campaigns');
        $where="typeid IN({$type_ids}) AND status=1";
        if($cam_m->where($where)->count()>0){
            //分类下有内容 不允许删除
            E('_CAM_TYPE_DEL_DIE_ERR');
            return false;
        }
        return true;
    }
    /**
     * 验证数据
     */
    private function _validation_save_data(&$post, &$add, &$save) {
        //验证分类个数不操过 10个
        if (count($post['save_data']) > 10) {
            //抛出分类不能超过10个
            E('_CAM_TYPE_SUM_MAX_ERR');
            return FALSE;
        }
        //开始验证
        foreach ($post['save_data'] as $cate) {
            //验证标题
            if (!isset($cate['title']) || trim($cate['title'], " \t\r\n") == '') {
                //标题不能为空
                E('_CAM_TYPE_TITLE_NOT_NULL_ERR');
            }
            $cate['title'] = trim($cate['title'], " \t\r\n");
            if (mb_strlen($cate['title'], 'utf8') > 10) {
                //分类名不能超过10个字
                E('_CAM_TYPE_TITLE_MAX_ERR');
            }
            //验证分类id
            if (!isset($cate['id'])) {
                //分类ID不能为空
                E('_CAM_TYPE_ID_NOT_NULL_ERR');
            }
            if (!preg_match('/^\d{1,}\d{0,}$/', $cate['id'])) {
                //分类必须为数字
                E('_CAM_TYPE_ID_IS_NUM_ERR');
            }
            //验证排序号
            if (!isset($cate['order_sort'])) {
                //排序号不能为空
                E('_CAM_TYPE_SORT_NOT_NULL_ERR');
            }
            if (!preg_match('/^\d{1,}\d{0,}$/', $cate['order_sort'])) {
                //排序号必须为数字
                E('_CAM_TYPE_SORT_IS_NUM_ERR');
            }
            //筛选添加和保存的数据
            if ($cate['id'] == 0) {
                $cate['created'] = NOW_TIME;
                $add[] = $cate;
            } else {
                $cate['updated'] = NOW_TIME;
                $save[] = $cate;
            }
        }
    }

}
