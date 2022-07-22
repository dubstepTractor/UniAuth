<?php declare(strict_types = 1); namespace uni\auth\task\async;

use uni\auth\data\Account;
use uni\auth\data\provider\mysql\AccountBridge;

use pocketmine\scheduler\AsyncTask;

use function serialize;
use function unserialize;

class AccountUpdateTask extends AsyncTask {

	/**
	 * @var string
	 */
	private $serialized_account;

	/**
	 *  _            _
	 * | |____ _ ___| | __
	 * |  _/ _' / __| |/ /
	 * | || (_) \__ \   <
	 *  \__\__,_|___/_|\_\
	 *
	 *
	 * @param Account $account
	 */
	function __construct(Account $account) {
		$this->serialized_account = serialize($account);
	}

	function onRun() {
		AccountBridge::updateAccount(unserialize($this->serialized_account));
	}
}