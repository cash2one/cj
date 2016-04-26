<?php
/**
 * SalesTypeModel.class.php
 * $author$
 */

namespace Sales\Model;

class SalesTypeModel extends AbstractModel
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->prefield = 'stp_';
    }
    
}
