<?php declare(strict_types = 1); namespace uni\auth;

use uni\auth\form\FormFactory;
use uni\auth\command\ResetCommand;

use uni\auth\data\Account;
use uni\auth\data\provider\AccountProvider;

use uni\auth\event\listener\block\BlockBreakListener;
use uni\auth\event\listener\block\BlockPlaceListener;

use uni\auth\event\listener\entity\EntityDamageListener;

use uni\auth\event\listener\player\PlayerChatListener;
use uni\auth\event\listener\player\PlayerJoinListener;
### uni\auth\event\listener\player\PlayerMoveListener;
use uni\auth\event\listener\player\PlayerQuitListener;
use uni\auth\event\listener\player\PlayerDropItemListener;
use uni\auth\event\listener\player\PlayerPreLoginListener;
use uni\auth\event\listener\player\PlayerInteractListener;
use uni\auth\event\listener\player\PlayerItemConsumeListener;
use uni\auth\event\listener\player\PlayerToggleSprintListener;
use uni\auth\event\listener\player\PlayerCommandPreprocessListener;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;

use Exception;

class Manager extends PluginBase {

	/**
	 *                    __ _                      _   _
	 *   ___  ___  _ __ _/ _(_) __ _ _   _ _ ____ _| |_(_) ___  _ __
	 *  / __\/ _ \| '_ \   _| |/ _' | | | | '_/ _' |  _| |/ _ \| '_ \
	 * | (__| (_) | | | | | | | (_) | |_| | || (_) | |_| | (_) | | | |
	 *  \___/\___/|_| |_|_| |_|\__  |\___/|_| \__,_|\__|_|\___/|_| |_|
	 *                         /___/
	 *
	 * @todo create config?
	 */
	public const MYSQL_HOSTNAME = '127.0.0.1';
	public const MYSQL_USERNAME = 'user';
	public const MYSQL_PASSWORD = 'pass';
	public const MYSQL_DATABASE = 'base';

	public const PASSWORD_LENGHT_MIN = 6;
	public const PASSWORD_LENGHT_MAX = 16;

	public const MAX_ERROR_COUNT        = 3;
	public const MAX_ATTEMPT_COUNT      = 5;
	public const MAX_INACTIVE_TIME      = 259200;
	public const MAX_AUTHORIZATION_TIME = 60;
	public const MAX_ADDRESS_IN_CHAIN   = 2;

	public const BAD_NICKNAME_ENTRY = [];
	public const PASSWORD_REGEXP    = '#[^\s\da-z]#is';

	/**
	 * @todo implement Formatter
	 */
	public const PREFIX_SUCCESS = "§l§a(!)§r ";
	public const PREFIX_ERROR   = "§l§c(!)§r ";
	public const PREFIX_INFO    = "§l§e(!)§r ";

	/**
	 * @var AccountProvider
	 */
	private $account_provider;

	/**
	 * @var FormFactory
	 */
	private $form_factory;

	/**
	 *
	 *  _ __ _   __ _ _ __   __ _  __ _  ___ _ __
	 * | '  ' \ / _' | '_ \ / _' |/ _' |/ _ \ '_/
	 * | || || | (_) | | | | (_) | (_) |  __/ |
	 * |_||_||_|\__,_|_| |_|\__,_|\__, |\___|_|
	 *                            /___/
	 *
	 */
	public function onEnable(): void {
		$this->loadProvider();
		$this->loadFormFactory();
		$this->loadListener();
		$this->loadCommand();
	}

	private function loadProvider(): void {
		$this->account_provider = new AccountProvider();
	}

	private function loadFormFactory(): void {
		$this->form_factory = new FormFactory($this);
	}

	private function loadListener(): void {
		$list = [
			new BlockBreakListener($this),
			new BlockPlaceListener($this),

			new EntityDamageListener($this),

			new PlayerChatListener($this),
			new PlayerJoinListener($this),
			### PlayerMoveListener($this),
			new PlayerQuitListener($this),
			new PlayerDropItemListener($this),
			new PlayerPreLoginListener($this),
			new PlayerInteractListener($this),
			new PlayerItemConsumeListener($this),
			new PlayerToggleSprintListener($this),
			new PlayerCommandPreprocessListener($this)
		];

		foreach($list as $listener) {
			$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		}
	}

	private function loadCommand(): void {
		$list = [
			new ResetCommand($this)
		];

		foreach($list as $command) {
			$map     = $this->getServer()->getCommandMap();
			$replace = $map->getCommand($command->getName());

			if(isset($replace)) {
				$replace->setLabel('');
				$replace->unregister($map);
			}

			$map->register($this->getName(), $command);
		}
	}

	/**
	 * @return AccountProvider
	 */
	private function getAccountProvider(): AccountProvider {
		return $this->account_provider;
	}

	/**
	 * @return FormFactory
	 */
	public function getFormFactory(): FormFactory {
		return $this->form_factory;
	}

	/**
	 *              _
	 *   __ _ ____ (_)
	 *  / _' |  _ \| |
	 * | (_) | (_) | |
	 *  \__,_|  __/|_|
	 *       |_|
	 *
	 * @param  string $nick
	 * @param  bool   $ignore_bridge
	 *
	 * @return Account|null
	 */
	public function getAccount(string $nick, bool $ignore_bridge = false): ?Account {
		return $this->getAccountProvider()->getAccount($nick, $ignore_bridge);
	}

	/**
	 * @param Account $account
	 * @param bool    $ignore_bridge
	 */
	public function saveAccount(Account $account, bool $ignore_bridge = false): void {
		$this->getAccountProvider()->setAccount($account, $ignore_bridge);
	}

	/**
	 * @param  Player $player
	 * @param  string $pass
	 * @param  bool   $use_xuid
	 *
	 * @return Account
	 */
	public function createAccount(Player $player, string $pass, bool $use_xuid = false): Account {
		return Account::create($player, $pass, $use_xuid);
	}

	/**
	 * @param string $nick
	 * @param bool   $ignore_bridge
	 */
	public function removeAccount(string $nick, bool $ignore_bridge = false): void {
		$this->getAccountProvider()->removeAccount($nick, $ignore_bridge);
	}
}