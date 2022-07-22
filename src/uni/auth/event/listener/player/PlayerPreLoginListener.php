<?php declare(strict_types = 1); namespace uni\auth\event\listener\player;

use uni\auth\Manager;
use uni\auth\event\listener\ManagedListener;

use pocketmine\event\player\PlayerPreLoginEvent as Event;

use function time;
use function substr;
use function stripos;

class PlayerPreLoginListener extends ManagedListener {

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
		$player = $event->getPlayer();
		$nick   = $player->getLowerCaseName();

		if(substr($nick, 0, 1) == ' ' or substr($nick, -1) == ' ') {
			$this->cancelEvent($event, Manager::PREFIX_ERROR. "У нас запрещены никнеймы начинающиеся или заканчивающиеся пробелом");
			return;
		}

		foreach(Manager::BAD_NICKNAME_ENTRY as $entry) {
			if(stripos($nick, $entry) !== false) {
				$this->cancelEvent($event, Manager::PREFIX_ERROR. "Пожалуйста, смените никнейм");
				return;
			}
		}

		$main = $this->getManager();

		foreach($main->getServer()->getOnlinePlayers() as $target) {
			$target_nick = $target->getLowerCaseName();

			if($nick != $target_nick) {
				continue;
			}

			if($player === $target) {
				continue;
			}

			$account = $main->getAccount($target_nick, true);

			if(!isset($account)) {
				$this->cancelEvent($event, Manager::PREFIX_ERROR. "Игрок §c$nick §rпроходит регистрацию");
				return;
			}

			if($account->isLogined()) {
				$this->cancelEvent($event, Manager::PREFIX_ERROR. "Игрок §c$nick §rуже играет на сервере");
				return;
			}

			$time = time() - $account->getJoinTimestamp();

			if($time < Manager::MAX_AUTHORIZATION_TIME) {
				$this->cancelEvent($event, Manager::PREFIX_ERROR. "Игрок §c$nick §rпроходит авторизацию");
				return;
			}

			$target->kick(Manager::PREFIX_ERROR. "Время для ввода пароля истекло", false);
		}
	}

	/**
	 * @param Event  $event
	 * @param string $message
	 */
	private function cancelEvent(Event $event, string $message): void {
		$account = $this->getManager()->getAccount($event->getPlayer()->getLowerCaseName(), true);

		if(isset($account)) {
			/**
			 * don't deauthorize normal players.
			 */
			$this->getManager()->saveAccount($account->setProtected(true), true);
		}

		$event->setKickMessage($message);
		$event->setCancelled();
	}
}