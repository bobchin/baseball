<?php

class BaseballShell extends AppShell
{
	public $uses = array('NPB');

	public function main()
	{
		set_time_limit(300);

		$teams = $this->NPB->getTeam();
		$types = $this->NPB->getType();

		$this->hr();
		$this->out('更新開始');
		$this->hr();

		foreach ($teams as $team) {
			foreach ($types as $type) {
				$teamName = $this->NPB->getTeam($team, true);
				$typeName = $this->NPB->getType($type, true);
				$this->out(sprintf('%s の %s を取得開始', $teamName, $typeName));

				if ($this->NPB->savePlayers($team, $type) === false) {
					$this->out('更新に失敗しました。');
				} else {
					$this->out('更新成功');
				}
				$this->hr();
			}
		}

		$this->hr();
		$this->out('更新終了！');
		$this->hr();
	}

	public function out($message = null, $newlines = 1, $level = Shell::NORMAL)
	{
		$message = mb_convert_encoding($message, 'SJIS', 'UTF8');
		parent::out($message, $newlines, $level);
	}
}




