<?php declare(strict_types = 1); namespace uni\auth\event\listener;

use uni\auth\Manager;

use pocketmine\event\Listener;
use pocketmine\event\Event;
use pocketmine\Player;

abstract class ManagedListener implements Listener {

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 *  _ _      _
	 * | (_)____| |_____ _ __   ___ _ __
	 * | | / __/   _/ _ \ '_ \ / _ \ '_/
	 * | | \__ \| ||  __/ | | |  __/ |
	 * |_|_|___/ \__\___|_| |_|\___|_|
	 *
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		$this->manager = $main;
	}

	/**
	 * @param  Player $player
	 *
	 * @return bool
	 */
	protected function isLogined(Player $player): bool {
		$account = $this->getManager()->getAccount($player->getLowerCaseName(), true);

		return isset($account) and $account->isLogined();
	}

	/**
	 * @param  Event  $event
	 * @param  Player $player
	 *
	 * @return bool
	 */
	protected function handleLoginCheck(Event $event, Player $player): bool {
		if($this->isLogined($player)) {
			return true;
		}

		$event->setCancelled();
		return false;
	}

	/**
	 * @param  Event  $event
	 * @param  Player $player
	 *
	 * @return bool
	 */
	protected function handleFormSendCheck(Event $event, Player $player): bool {
		$main    = $this->getManager();
		$factory = $main->getFormFactory();

		$player  = $event->getPlayer();
		$account = $main->getAccount($player->getLowerCaseName(), true);

		if(!isset($account)) {
			$factory->sendRegisterForm($player);
			$event->setCancelled();
			return false;
		}

		if(!$account->isLogined()) {
			$factory->sendLoginForm($player);
			$event->setCancelled();
			return false;
		}

		return true;
	}

	/**
	 * @return Manager
	 */
	protected function getManager(): Manager {
		return $this->manager;
	}
}