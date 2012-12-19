<?php

class BaseballShell extends AppShell
{
	public $uses = array('Npb');

	public function main()
	{
		set_time_limit(300);

		$teams = $this->Npb->getTeam();
		$types = $this->Npb->getType();

        $this->hr();
        $start = new DateTime();
		$this->out('更新開始' . date('Y-m-d H:i:s'));

		foreach ($teams as $team) {
			foreach ($types as $type) {
				$teamName = $this->Npb->getTeam($team, true);
				$typeName = $this->Npb->getType($type, true);
				$this->out(sprintf('%s の %s を取得開始', $teamName, $typeName));

				if ($this->Npb->savePlayers($team, $type) === false) {
					$this->out('更新に失敗しました。');
				} else {
					//$this->out('更新成功');
				}
				//$this->hr();
			}
		}

        $this->out('更新終了！' . date('Y-m-d H:i:s'));
        $end = new DateTime();
        $diff = date_diff($start, $end);
        $this->out(sprintf('%s', $diff->format('%s sec.')));
		$this->hr();
	}

	public function out($message = null, $newlines = 1, $level = Shell::NORMAL)
	{
		$message = mb_convert_encoding($message, 'SJIS', 'UTF8');
		parent::out($message, $newlines, $level);
	}
}




