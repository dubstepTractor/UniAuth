<?php declare(strict_types = 1); namespace uni\auth\event\listener\player;

use uni\auth\Manager;
use uni\auth\event\listener\ManagedListener;
use uni\auth\event\account\AccountLoginEvent;

use pocketmine\event\player\PlayerJoinEvent as Event;

use function time;

class PlayerJoinListener extends ManagedListener {

	/**
	 *  _ _      _
	 * | (_)____| |_____ _ __   ___ _ __
	 * | | / __/   _/ _ \ '_ \ / _ \ '_/
	 * | | \__ \| ||  __/ | | |  __/ |
	 * |_|_|___/ \__\___|_| |_|\___|_|
	 *
	 *
	 * @param Event $event
	 *
	 * @priority        LOWEST
	 * @ignoreCancelled FALSE
	 */
	public function onCall(Event $event): void {
		$main    = $this->getManager();
		$factory = $main->getFormFactory();

		$player  = $event->getPlayer();
		$nick    = $player->getLowerCaseName();
		$account = $main->getAccount($nick);
		
		if(!isset($account)) {
			$factory->sendRegisterForm($player);
			return;
		}

		$xuid    = $player->getXuid();
		$address = $player->getAddress();

		/**
		 * @todo reformat?
		 */
		if($account->hasXuid()) {
			if(!$account->verifyXuid($xuid)) {
				$factory->sendLoginForm($player);
				return;
			}
		} else {
			if(!$account->verifyAddress($address)) {
				$factory->sendLoginForm($player);
				return;
			}
		}

		$count = $account->getAttemptCount();

		if($count > Manager::MAX_ATTEMPT_COUNT) {
			$factory->sendLoginForm($player);
			return;
		}

		$time = time() - $account->getLoginTimestamp();

		if($time > Manager::MAX_INACTIVE_TIME) {
			$factory->sendLoginForm($player);
			return;
		}

		/**
		 * @todo reformat
		 */
		$account->getAddressChain()->add($player->getAddress());

		$event = new AccountLoginEvent($main, $account->setLogined());

		$event->call();

		if($event->isCancelled()) {
			$factory->sendLoginForm($player);
			return;
		}

		$main->saveAccount($event->getAccount());

		$player->addTitle(" ", "§aПриятной игры");
	}
}