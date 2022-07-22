<?php declare(strict_types = 1); namespace uni\auth\task\async;

use uni\auth\data\provider\mysql\AccountBridge;

use pocketmine\scheduler\AsyncTask;

class AccountDeleteTask extends AsyncTask {

	/**
	 * @var string
	 */
	private $nickname;

	/**
	 *  _            _
	 * | |____ _ ___| | __
	 * |  _/ _' / __| |/ /
	 * | || (_) \__ \   <
	 *  \__\__,_|___/_|\_\
	 *
	 *
	 * @param string $nick
	 */
	function __construct(string $nick) {
		$this->nickname = $nick;
	}

	function onRun() {
		AccountBridge::deleteAccount($this->nickname);
	}
}