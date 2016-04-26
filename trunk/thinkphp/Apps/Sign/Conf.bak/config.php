<?php
return array(
	//'配置项'=>'配置值'
	'add_begin_time' => "3", // 工作时间前*小时
	'add_end_time' => "9", // 工作时间后*小时

    // 前台菜单配置
    'MENU_FRONTEND' => array(),

    'MENU_ADMINCP' => array(
        'list' => array(
            'icon' => 'fa-list', 'name' => '公司考勤记录', 'display' => 1,
            'subnavdisplay' => 1, 'displayorder' => 107, 'default' => 1,
        ),
        'detail' => array(
            'icon' => 'fa-eye', 'name' => '考勤详情', 'display' => 1,
            'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
        ),
        'upposition' => array(
            'icon' => 'fa-list', 'name' => '外出考勤记录', 'display' => 1,
            'subnavdisplay' => 1, 'displayorder' => 105, 'default' => 0,
        ),
        'updetail' => array(
            'icon' => 'fa-eye', 'name' => '外勤详情', 'display' => 1,
            'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
        ),
        'wxcpmenu' => array(
            'icon' => 'fa-gear', 'name' => '微信菜单设置', 'display' => 1,
            'subnavdisplay' => 0, 'displayorder' => 108, 'default' => 0,
        ),
        'badd' => array(
            'icon' => 'fa-gear', 'name' => '添加班次', 'display' => 1,
            'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
        ),
        'bdelete' => array(
            'icon' => 'fa-times', 'name' => '删除班次', 'display' => 1,
            'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
        ),
        'blist' => array(
            'icon' => 'fa-gear', 'name' => '班次安排', 'display' => 1,
            'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 0,
        ),
        'config' => array(
            'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
            'subnavdisplay' => 1, 'displayorder' => 110, 'default' => 0,
        ),
        'schedule' => array(
            'icon' => 'fa-list', 'name' => '人员排班', 'display' => 1,
            'subnavdisplay' => 1, 'displayorder' => 109, 'default' => 0,
        )
    ),

    // 微信企业号自定义菜单
    'MENU_QYWX' => array(
        array(
            'type' => 'view', 'name' => '公司考勤',
            'url' => '{domain_url}/frontend/sign/index/?pluginid={pluginid}',
        ),
        array(
            'type' => 'view', 'name' => '外出考勤',
            'url' => '{domain_url}/frontend/sign/uplocation/?pluginid={pluginid}',
        ),
        array(
            'type' => 'view', 'name' => '考勤记录',
            'url' => '{domain_url}/frontend/sign/signsearch/?pluginid={pluginid}',
        )
    )


);
