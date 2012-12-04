<?php
App::load('SimpleHtmlDom/simple_html_dom');

class NPB extends AppModel
{
	public $name = 'NPB';
	public $useTable = false;

	/**
	 * 種類（投手・野手・守備）
	 */
	static public $PITCHER = 'p';
	static public $BATTER  = 'b';
	static public $DEFENCE = 'f';

	/**
	 * チーム
	 */
	static public $HAWKS    = 'h';
	static public $FIGHTERS = 'f';
	static public $LIONS    = 'l';
	static public $BUFFALOS = 'bs';
	static public $EAGLES   = 'e';
	static public $MARINES  = 'm';

	static public $DRAGONS  = 'd';
	static public $SWALLOWS = 's';
	static public $GIANTS   = 'g';
	static public $TIGARS   = 't';
	static public $CARPS    = 'c';
	static public $BAYSTARS = 'db';

	private $parsers = array();

	/**
	 * パーサを初期化。URLを格納しておく。
	 */ 
	public function __construct()
	{
		$teams = $this->getTeam();
		$types = $this->getType();

		foreach ($teams as $team) {
			foreach ($types as $type) {
				$key = $this->parserKey($team,  $type);
				$this->parsers[$key] = $this->getUrl($team, $type);
			}
		}
	}

	/**
	 * パーサ格納用配列のキー
	 */
	private function parserKey($team, $type)
	{
		return $team . '_' . $type;
	}

	/*
	 * 投手・野手・守備の各タイトルとDBフィールドのマッピング
	 */
	public function getKeyMap($type)
	{
		switch ($this->getType($type)) {
			case self::$PITCHER:
				return array(
					'投手' 		=> 'player',
					'登板' 		=> 'appearances',
					'勝利' 		=> 'wins',
					'敗北' 		=> 'losses',
					'セ｜ブ' 	=> 'save',
					'ホ｜ル' 	=> 'hold',
					'ＨＰ' 		=> 'holdpoint',
					'完投' 		=> 'complete_games',
					'完封勝' 	=> 'shutouts',
					'無四球' 	=> 'non_walk',
					'勝率' 		=> 'winning_percentage',
					'打者' 		=> 'batters_faced',
					'投球回' 	=> 'innings_pitched',
					'安打' 		=> 'hits',
					'本塁打' 	=> 'homeruns',
					'四球' 		=> 'bases_on_balls',
					'故意四' 	=> 'intentional_walks',
					'死球' 		=> 'hit_by_pitch',
					'三振' 		=> 'strikeouts',
					'暴投' 		=> 'wild_pitches',
					'ボ｜ク' 	=> 'balks',
					'失点' 		=> 'runs',
					'自責点' 	=> 'earned_runs',
					'防御率' 	=> 'earned_run_average',
				);
				break;
			
			case self::$DEFENCE:
				return array(
					'【一塁手】' => 'player',
					'試合'		=> 'games',
					'刺殺'		=> 'put_outs',
					'捕殺'		=> 'assists',
					'失策'		=> 'errors',
					'併殺'		=> 'double_plays',
					'捕逸'		=> 'passed_balls',
					'守備率'	=> 'fielding_average',
				);
				break;

			default:
				return array(
					'選手' 		=> 'player',
					'試合' 		=> 'game',
					'打席' 		=> 'plate_appearance',
					'打数' 		=> 'at_bats',
					'得点' 		=> 'run_score',
					'安打' 		=> 'hits',
					'二塁打' 	=> 'base2',
					'三塁打' 	=> 'base3',
					'本塁打' 	=> 'homeruns',
					'塁打' 		=> 'totale_bases',
					'打点' 		=> 'runs_batted_in',
					'盗塁' 		=> 'stolen_bases',
					'盗塁刺' 	=> 'caught_stealling',
					'犠打' 		=> 'sacrifice_hits',
					'犠飛' 		=> 'sacrifice_flies',
					'四球' 		=> 'bases_on_balls',
					'故意四' 	=> 'intentional_walks',
					'死球' 		=> 'hit_by_pitch',
					'三振' 		=> 'strikeouts',
					'併殺打' 	=> 'double_plays',
					'打率' 		=> 'batting_average',
					'長打率' 	=> 'slugging_percentage',
					'出塁率' 	=> 'on_base_percentage',
				);
				break;
		}
	}

	/*
	 * チーム名の短縮名を取得
	 */
	public function getTeam($team = null)
	{
		$teams = array(
			self::$HAWKS,
			self::$FIGHTERS,
			self::$LIONS,
			self::$BUFFALOS,
			self::$EAGLES,
			self::$MARINES,

			self::$DRAGONS,
			self::$SWALLOWS,
			self::$GIANTS,
			self::$TIGARS,
			self::$CARPS,
			self::$BAYSTARS,
		);

		if (!is_string($team)) {
			return $teams;
		}

		if (!in_array($team, $teams)) {
			$team = self::$HAWKS;
		}
		return $team;
	}

	/**
	 * 種類を取得
	 */
	public function getType($type = null)
	{
		$types = array(
			self::$BATTER, 
			self::$PITCHER, 
			self::$DEFENCE,
		);

		if (!is_string($type)) {
			return $types;
		}

		if (!in_array($type, $types)) {
			$type = self::$BATTER;
		}
		return 	$type;
	}

	/**
	 * データ取得用のURL
	 */
	private function getUrl($team, $type)
	{
		$urls = array(
			self::$BATTER  => 'http://bis.npb.or.jp/2012/stats/idb1_%s.html',
			self::$PITCHER => 'http://bis.npb.or.jp/2012/stats/idp1_%s.html',
			self::$DEFENCE => 'http://bis.npb.or.jp/2012/stats/idf1_%s.html',
		);

		$type = $this->getType($type);
		return sprintf($urls[$type], $this->getTeam($team));
	}

	/**
	 * モデル名を取得
	 */
	private function getModel($type)
	{
		switch ($this->getType($type)) {
			case self::$PITCHER:
				$result = 'Pitcher';
				break;
			
			case self::$DEFENCE:
				$result = 'Defence';
				break;
			
			default:
				$result = 'Batter';
				break;
		}

		return $result;
	}

	/**
	 * パーサを取得する
	 *
	 * 一度取得したものは再取得しない
	 */
	private function getParser($team, $type)
	{
		$key = $this->parserKey($team,  $type);

		if (isset($this->parsers[$key])) {
			if (is_string($this->parsers[$key]) && $obj = file_get_html($this->parsers[$key])) {
				$this->parsers[$key] = $obj;
			}

			if (is_object($this->parsers[$key])) {
				return $this->parsers[$key];
			}
		}

		return false;
	}

	/**
	 * 取得したデータからヘッダ情報を抜き出す
	 */
	private function getHeader($team, $type)
	{
		$parser = $this->getParser($team, $type);

		$result = array();

		$tr = $parser->find('div#stdivmaintbl tr', 1);
		foreach ($tr->find('th') as $e) {
			$s = $this->encode($e->innertext);
			$s = preg_replace('/<br>|　|\s/i', '', $s);
			$result[] = $s;
		}
		return $result;
	}

	/**
	 * ヘッダ情報からDBフィールドのリストを作成
	 */
	private function getHeaderKey($team, $type)
	{
		$headers = $this->getHeader($team, $type);
		$keys = $this->getKeyMap($type);

		$result = array();
		if ($this->getType($type) == self::$DEFENCE) {
			$result[] = false;
		}

		foreach ($headers as $header) {
			if (!empty($header) && isset($keys[$header])) {
				$result[] = $keys[$header];
			} else {
				$result[] = false;
			}
		}
		return $result;
	}

	/**
	 * 取得したデータから選手情報を抜き出す
	 */
	private function getPlayers($team, $type)
	{
		$keys = $this->getHeaderKey($team, $type);

		$parser = $this->getParser($team, $type);
		$model = $this->getModel($type);

		$result = array();
		foreach ($parser->find('tr.ststats') as $e) {
			$player = array();
			foreach ($e->find('td') as $p) {
				$player[] = $this->encode($p->innertext);
			}
			$player = $this->mapPlayerToKey($player, $keys);
			$player['team'] = $team;
			$result[] = array($model => $player);
		}
		return $result;
	}

	/**
	 * エンコードする
	 */
	private function encode($str)
	{
		return mb_convert_encoding($str, 'UTF-8', 'SJIS');
	}

	private function mapPlayerToKey($player, $keys)
	{
		$result = array();
		foreach ($keys as $i => $k) {
			if ($k === false) {
				continue;
			}
			$result[$k] = isset($player[$i])? $player[$i]: '';
		}
		return $result;
	}

	public function savePlayers($team, $type)
	{
		$result = true;

		$model = $this->getModel($type);
		App::import('Model', $model);
		$obj = new $model();

		$data = $this->getPlayers($team, $type);

		foreach ($data as $player) {
			$row = $obj->find('first', array(
				'conditions' => array('player like' => '%' . $player[$model]['player'] . '%'),
			));

			if (empty($row)) {
				$obj->create();
			} else {
				$obj->set($player);
				$obj->id = $row[$model]['id'];
			}
			
			if ($obj->save($player) === false) {
				$result = false;
			}
		}

		return $result;
	}

}


