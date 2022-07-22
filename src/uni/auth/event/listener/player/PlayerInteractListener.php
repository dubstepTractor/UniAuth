<?php declare(strict_types = 1); namespace uni\auth\event\listener\player;

use uni\auth\event\listener\ManagedListener;

use pocketmine\event\player\PlayerInteractEvent as Event;

class PlayerInteractListener extends ManagedListener {

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
		if($event->getAction() != Event::RIGHT_CLICK_BLOCK) {
			return;
		}

		$this->handleFormSendCheck($event, $event->getPlayer());
	}
}