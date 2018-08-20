<?php
class Project extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	public function index() {
		if ( !post('openid') ) return api_error(5001);
		else $this->_getAllGame(post());
	}
	private function _getAllGame($data = array()) {
		$res = array(
			'subscribe' => 1
		);
		$games = array();
		$resgames = $this->redis->hgetallvalue('games');
		$resgames = $resgames ? $resgames : array();
		$ressubscribe = $this->redis->hget('subscribe',$data['openid']);
		if ( !$ressubscribe ) $res['subscribe'] = 0;
		foreach ( $resgames as $item ) {
			foreach ( $item as $item1 ) {
				$item1['subscribe_style'] = array(
					'sub' => array(
						'bgcolor' => '#FFF'
					)
				);
				array_push($games,$item1);
			}
		}
		$res['games'] = $games;
		$resuserresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserresult = $resuserresult ? $resuserresult : array();
		foreach ( $res['games'] as $item ) {
			$realid = decrypt($item['id']);
			$item['gameresult_state'] = 0;
			foreach ( $resuserresult as $one ) {
				if ( $one['game_id'] == $realid ) {
					$item['gameresult_state'] = 1;
					break;
				}
			}
		}
		$resuserrecordstate = $this->redis->hget('userrecordstate',$data['openid']);
		if ( !$resuserrecordstate || $resuserrecordstate['record_state'] == 0 ) {
			$res['record_state'] = 0;
			return api_success($res);
		}
		else {
			foreach ( $resuserresult as $item ) {
				$resgame = $this->redis->hget('game',$item['game_id']);
				if ( !$resgame ) continue;
				else {
					$resrecordgamestate = $this->redis->hget('userrecordgamestate',$data['openid'] . $item['game_id']);
					if ( !$resrecordgamestate || $resrecordgamestate['gamerecord_state'] == 0 ) continue;
					else {
						$res['record_state'] = 1;
						return api_success($res);
					}
				}
			}
			$res['record_state'] = 0;
			return api_success($res);
		}
	}
}