<?php declare(strict_types = 1); namespace uni\auth\event\account;

use uni\auth\Manager;
use uni\auth\data\Account;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

use pocketmine\Player;

class AccountEvent extends PluginEvent implements Cancellable {

	/**
	 * @var Account
	 */
	private $account;

	/**
	 *                        _
	 *   _____    _____ _ __ | |__
	 *  / _ \ \  / / _ \ '_ \|  _/
	 * |  __/\ \/ /  __/ | | | |_
	 *  \___/ \__/ \___|_| |_|\__\
	 *
	 *
	 * @param Manager $main
	 * @param Account $account
	 */
	public function __construct(Manager $main, Account $account) {
		parent::__construct($main);

		$this->account = $account;
	}

	/**
	 * @return Account
	 */
	public function getAccount(): Account {
		return $this->account;
	}

	/**
	 * @return Player|null
	 */
	public function getPlayer(): ?Player {
		return $this->getPlugin()->getServer()->getPlayerExact($this->getAccount()->getNickname());
	}
}