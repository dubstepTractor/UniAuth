<?php declare(strict_types = 1); namespace uni\auth\event\listener\entity;

use uni\auth\event\listener\ManagedListener;

use pocketmine\event\entity\EntityDamageEvent as Event;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class EntityDamageListener extends ManagedListener {

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
		$entity = $event->getEntity();

		if(!$entity instanceof Player) {
			return;
		}

		if(!$this->handleLoginCheck($event, $entity)) {
			return;
		}

		if(!$event instanceof EntityDamageByEntityEvent) {
			return;
		}

		$damager = $event->getDamager();

		if(!$damager instanceof Player) {
			return;
		}

		$this->handleLoginCheck($event, $damager);
	}
}