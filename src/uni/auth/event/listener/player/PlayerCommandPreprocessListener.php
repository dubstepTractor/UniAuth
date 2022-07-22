<?php declare(strict_types = 1); namespace uni\auth\event\listener\player;

use uni\auth\event\listener\ManagedListener;

use pocketmine\event\player\PlayerCommandPreprocessEvent as Event;

class PlayerCommandPreprocessListener extends ManagedListener {

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
	 * @priority        LOW
	 * @ignoreCancelled FALSE
	 */
	public function onCall(Event $event): void {
		$message = $event->getMessage();

		if($message[0] != '/' and !($message[0] == '.' and $message[1] == '/')){
			return;
		}

		$this->handleFormSendCheck($event, $event->getPlayer());
	}
}