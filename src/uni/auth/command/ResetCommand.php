<?php declare(strict_types = 1); namespace uni\auth\command;

use uni\auth\Manager;
use uni\auth\event\account\AccountResetEvent;

use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

use function strlen;
use function array_map;
use function array_shift;

class ResetCommand extends Command {

	private const NAME = 'reset';

	private const PERMISSION        = 'uniauth.command.reset';
	private const PERMISSION_PLAYER = 'uniauth.command.reset-player';

	private const DESCRIPTION        = '§eСбрасывает пароль от аккаунта';
	private const DESCRIPTION_PLAYER = 'Сбрасывает пароль от аккаунта указанного игрока';

	private const PERMISSION_LIST = [
		self::PERMISSION        => self::DESCRIPTION,
		self::PERMISSION_PLAYER => self::DESCRIPTION_PLAYER
	];

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 *                                             _
	 *   ___  ___  _ __ _  _ __ _   __ _ _ __   __| |
	 *  / __\/ _ \| '  ' \| '  ' \ / _' | '_ \ / _' |
	 * | (__| (_) | || || | || || | (_) | | | | (_) |
	 *  \___/\___/|_||_||_|_||_||_|\__,_|_| |_|\__,_|
	 *
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		parent::__construct(self::NAME, self::DESCRIPTION);

		$this->manager = $main;

		foreach(self::PERMISSION_LIST as $permission => $description) {
			$permission = new Permission($permission, $description);

			PermissionManager::getInstance()->addPermission($permission);
		}

		$this->setPermission(self::PERMISSION);
	}

	/**
	 * @param  CommandSender $sender
	 * @param  string        $label
	 * @param  string[]      $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, string $label, array $args) {
		$main = $this->getManager();

		if(empty($args)) {
			if(!$sender instanceof Player) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Используйте команду в игре");
				return true;
			}

			if(!$sender->hasPermission(self::PERMISSION)) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
				return true;
			}

			$nick    = $sender->getLowerCaseName();
			$account = $main->getAccount($nick);

			if(!isset($account)) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Вы еще не зарегистрированы");
				return true;
			}

			$event = new AccountResetEvent($main, $account);

			$event->call();

			if($event->isCancelled()) {
				return true;
			}

			$main->removeAccount($nick);
			$main->getFormFactory()->sendRegisterForm($sender);

			$sender->sendMessage(Manager::PREFIX_SUCCESS. "Аккаунт сброшен");
			return true;
		}

		if(!$sender->hasPermission(self::PERMISSION_PLAYER)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
			return true;
		}

		$args   = array_map('strtolower', $args);
		$target = array_shift($args);

		if(strlen($target) > 16) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Никнейм не является действительным");
			return true;
		}

		$account = $main->getAccount($target);

		if(!isset($account)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Указанный игрок еще не зарегистрирован");
			return true;
		}

		$event = new AccountResetEvent($main, $account);

		$event->call();

		if($event->isCancelled()) {
			return true;
		}

		$main->removeAccount($target);
		$sender->sendMessage(Manager::PREFIX_SUCCESS. "Аккаунт игрока §a$target §rсброшен");

		$player = $main->getServer()->getPlayerExact($target);

		if(isset($player)) {
			$main->getFormFactory()->sendRegisterForm($player);
			$player->sendMessage(Manager::PREFIX_INFO. "Ваш аккаунт сброшен");
		}

		return true;
	}

	/**
	 * @return Manager
	 */
	private function getManager(): Manager {
		return $this->manager;
	}
}