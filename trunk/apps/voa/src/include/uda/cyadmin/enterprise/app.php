<?php

/**
 * voa_uda_cyadmin_enterprise_app
 * uda/畅移后台/企业/应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_enterprise_app extends voa_uda_cyadmin_enterprise_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 通知 OA 企业站指定应用已完成审核操作
	 * <p>客服手动完成企业微信操作后，通过此方法来通知对应企业OA站应用开通成功，并标记本地应用处理状态</p>
	 *
	 * @param string $domain
	 * @param string $type
	 * @param number $ea_id
	 *
	 * @return boolean
	 */
	public function post_to_oasite( $domain, $type, $ea_id ) {

		$app = $this->serv_enterprise_app->fetch( $ea_id );
		if( empty( $app ) ) {
			$this->errmsg( 201, '指定客服应用处理 ID 不存在（eaid = ' . $ea_id . '）' );
		}

		$args = array(
			'cp_pluginid'            => $app['oacp_pluginid'],
			'qywx_application_agent' => array(
				'agentid' => $app['ea_agentid'],
				'cyea_id' => $ea_id
			)
		);

		// 映射对应应用操作状态所使用的远程企业OA接口的对应方法
		$method = '';
		switch( rstrtolower( $type ) ) {
			case 'open':
			case 'wait_open':
				$method = 'app_open_confirm';
				break;
			case 'close':
			case 'wait_close':
				$method = 'app_close_confirm';
				break;
			case 'delete':
			case 'wait_delete':
				$method = 'app_delete_confirm';
				break;
		}

		if( ! $method ) {
			$this->errmsg( 202, '未知的客服通知类型' );
		}

		$oa_result = array();
		// 使用企业OA站接口来完成通知
		if( $this->qyoa_api( $domain, 'application', $method, $args, $oa_result ) ) {
			// 操作成功，则处理本地状态

			if( $this->operation_over( $ea_id, $type ) ) {
				// 操作成功
				return true;
			} else {
				$this->errmsg( 203, '通知企业 OA 站操作成功，但本地应用处理状态更新失败，操作请求:ea_id = ' . $ea_id . ', type=' . $type );

				return false;
			}

			return true;
		} else {
			// 操作失败
			$this->errmsg( 204, '通知企业OA站操作错误' );

			return false;
		}

	}

	/**
	 * 标记指定客服应用为某一完成状态
	 * <p>用于标记某个应用处理id状态，一般不会单独使用</p>
	 *
	 * @param number $ea_id
	 * @param string $type open|close|delete
	 *
	 * @return boolean
	 */
	public function operation_over( $ea_id, $type ) {

		if( $type == 'open' || $type == 'wait_open' ) {
			// 设置为开启
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_OPEN;
		} elseif( $type == 'close' || $type == 'wait_close' ) {
			// 设置为关闭
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_CLOSE;
		} elseif( $type == 'delete' || $type == 'wait_delete' ) {
			// 设置为删除
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_DELETE;
		} else {
			$this->error = '未知的处理状态';

			return false;
		}

		// 标记数据处理状态
		$app = array(
			'ea_appstatus' => $ea_appstatus
		);
		$this->serv_enterprise_app->update( $app, $ea_id );

		// 其他还有什么操作可以继续写在这里

		return true;
	}

	/**
	 * 添加一个等待处理的应用 for 企业oa站内部接口调用
	 * <p>为企业站OA通过内部方法调用所提供的接口服务，标记应用处理状态</p>
	 *
	 * @param array $application array('name' => '', 'status' => open|close|delete, 'icon' => '', 'description' => '', 'cp_pluginid' => 0, 'ep_id' => 0)
	 * @param string $status 状态 open|close|delete
	 * @param number $ea_id <strong style="color:red">(引用结果)</strong>应用处理id
	 *
	 * @return boolean
	 */
	public function operation_wait( $application = array(), $type, &$cyea_id ) {

		$ep_id = $application['ep_id'];
		if( $type == 'open' || $type == 'wait_open' ) {
			// 设置为待开启
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_OPEN;
		} elseif( $type == 'close' || $type == 'wait_close' ) {
			// 设置为待关闭
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_CLOSE;
		} elseif( $type == 'delete' || $type == 'wait_delete' ) {
			// 设置为待删除
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_WAIT_DELETE;
		} elseif( $type == 'confirm_open' ) {
			// 确定开启
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_OPEN;
		} elseif( $type == 'confirm_close' ) {
			// 确定关闭
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_CLOSE;
		} elseif( $type == 'confirm_delete' ) {
			// 确定删除
			$ea_appstatus = voa_d_cyadmin_enterprise_app::APPSTATUS_DELETE;
		} else {
			$this->error = '未知的处理状态';

			return false;
		}

		// 构造app表需要的数据
		$app_data = array(
			'ep_id'          => $ep_id,
			'ea_name'        => $application['name'],
			'ea_appstatus'   => $ea_appstatus,
			'ea_icon'        => $application['icon'],
			'ea_description' => $application['description'],
			'oacp_pluginid'  => $application['cp_pluginid'],
			'ea_agentid'     => $application['agentid'],
		);

		// 找到该企业OAid对应应用的申请记录
		$enterprise_app = $this->serv_enterprise_app->fetch_by_ep_id_and_cp_pluginid( $application['ep_id'], $application['cp_pluginid'] );
		if( empty( $enterprise_app ) ) {
			// 无记录则写入

			// 新增一条数据
			$cyea_id = $this->serv_enterprise_app->insert( $app_data, true );

		} else {
			// 有记录则更新
			$cyea_id = $enterprise_app['ea_id'];
			$this->serv_enterprise_app->update( $app_data, $cyea_id );
		}

		return true;
	}


}

