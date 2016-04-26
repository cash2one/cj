<?php

class voa_uda_cyadmin_content_train_setting extends voa_uda_cyadmin_content_base {
    private $__service_setting = null;

    public function __construct() {
        parent::__construct();
        if ($this->__service_setting == null) {
            $this->__service_setting = new voa_s_cyadmin_content_train_setting();
        }
    }

    public function get_all() {
        return $this->__service_setting->list_all();
    }

    /**
     * 添加培训设置字段
     * @pargm array $form
     *
     * @return bool
     */
    public function add($form) {
        $data = array();
        foreach ($form as $val) {

            $data[$val['name']] = $val['value'];
        }

        if (!isset($data['fieldname']) || !isset($data['fieldtype'])) {

            return 'isset error';
        }

        $data['fieldname'] = trim($data['fieldname']);
        $data['fieldtype'] = trim($data['fieldtype']);

        if (empty($data['fieldname']) || empty($data['fieldtype'])) {

            return 'empty error';
        }

        $is_exist = $this->__service_setting->get_by_conds(array(
            'fieldname' => $data['fieldname']
        ));
        // 如果该填写字段存在,则不添加
        if ($is_exist) {

            return 'exist error';
        }

        $is_add = $this->__service_setting->insert($data);
        if (count($is_add) > 0) {

            return array(
                'status' => 1,
                'data' => $is_add
            );
        } else {

            return array(
                'status' => 0
            );
        }
    }

    public function del($form) {
        $sid = (int)$form['sid'];

        if (!$sid) {

            return false;
        }

        $is_del = $this->__service_setting->delete($sid);

        if ($is_del) {

            return array(
                'status' => 1
            );
        } else {

            return array(
                'status' => 2
            );
        }
    }

    /**
     * 编辑培训设置字段
     * @pargm array $form
     * @return array
     */
    public function  edit($form) {

        $data = array();
        foreach ($form as $val) {

            $data[$val['name']] = $val['value'];
        }

        $sid = (int)$data['sid'];

        if (!$sid) {

            return false;
        }

        unset($data['sid']);
        $save = $this->__service_setting->update($sid, $data);

        if ($save) {

            return array(
                'status' => 1
            );

        } else {

            return array(
                'status' => 0
            );
        }

    }
}
