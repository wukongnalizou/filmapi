<?php
class Subscribe extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//订阅
	public function subscribeProject() {
		if ( !post('open_id') ) return api_error(5001);
		else $this->_subscribe(post());
	}
	//取消观看
	public function disconnect() {
		if ( !post('open_id') ) return api_error(5001);
		else $this->_disconnect(post());
	}
	//订阅/取消订阅(二期)
	private function _subscribe($data = array()) {
		$resubscribe = $this->redis->hget('subscribe',$data['open_id']);
		if ( $data['state'] == 0 ) {
			if ( !$resubscribe ) {
				$this->redis->hset('subscribe',$data['open_id'],array(
					'state' => 1,
					'sub_time' => time()
				));
			}
			rlog('addUserSubscribe',array(
				'open_id' => $data['open_id'],
				'pro_id' => 0
			));
			return api_success(array(
				'state' => 1,
				'msg' => '订阅成功'
			));
		}
		elseif ( $data['state'] == 1 ) {
			$this->redis->hdel('subscribe',$data['open_id']);
			rlog('deleteUserSubscribe',array(
				'open_id' => $data['open_id'],
				'pro_id' => 0
			));
			return api_success(array(
				'state' => 0,
				'msg' => '取消订阅成功'
			));
		}
		else return api_error(5001);
	}
	//取消观看
	private function _disconnect($data = array()) {
		$resuser = $this->redis->hget('user',$data['open_id']);
		$resusergametime = $this->redis->hget('usergametime',$resuser['id']);
		$resusergametime = $resusergametime ? $resusergametime : array();
		if ( $resusergametime['end'] == 0 ) {
			$day1 = day();
			$time1 = time();
			$userontime = array(
				'user_id' => $resuser['id'],
				'starttime' => $resusergametime['starttime'],
				'endtime' => $time1,
				'ontime' => $time1 - $resusergametime['starttime']
			);
			$this->redis->hset('usergametime',$resuser['id'],array(
				'starttime' => $time1,
				'end' => 1
			));
			rlog('over_tg_gameontime',$userontime);
		}
		return api_success('取消观看成功');
	}
}