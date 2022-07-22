<?php declare(strict_types = 1); namespace uni\auth\event\listener\player;

use uni\auth\event\listener\ManagedListener;

use pocketmine\event\player\PlayerChatEvent as Event;
use pocketmine\Player;

/**
 * @todo check timings
 */
class PlayerChatListener extends ManagedListener {

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
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	public function onCall(Event $event): void {
		$this->handleLoginCheck($event, $event->getPlayer());

		$main = $this->getManager();
		$list = [];

		foreach($event->getRecipients() as $player) {
			if(!($player instanceof Player)) {
				$list[] = $player;

				continue;
			}

			if(!$this->isLogined($player)) {
				continue;
			}

			$list[] = $player;
		}

		$event->setRecipients($list);
	}
}