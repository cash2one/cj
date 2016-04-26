<?php

/**
 *
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_epedit extends voa_c_cyadmin_base {

	public function execute() {
		$get = $this->request->getx();

		$agent    = $get['agent'];
		$epid     = $get['epid'];
		$oldagent = $get['oldagent'];

		$data['ep_agent'] = $get['agent'];
		$serv             = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		$serv->update( $data, $epid );
		$serv_account = &service::factory( 'voa_s_cyadmin_enterprise_account' );

		//旧代理商代理数量减一
		if( ! empty( $oldagent ) ) {
			$oldaccount       = $serv_account->get( $oldagent );
			$olddata['count'] = $oldaccount['count'] - 1;
			$serv_account->update( $oldagent, $olddata );
		}
		//新代理商代理数量加一
		if( ! empty( $agent ) ) {
			$account     = $serv_account->get( $agent );
			$da['count'] = $account['count'] + 1;
			$serv_account->update( $agent, $da );

		}

		return true;
	}

}
