<?php declare(strict_types = 1); namespace uni\auth\form;

use uni\auth\Manager;

use uni\auth\event\account\AccountLoginEvent;
use uni\auth\event\account\AccountRegisterEvent;

use Frago9876543210\EasyForms\elements\Input;
use Frago9876543210\EasyForms\elements\Label;
use Frago9876543210\EasyForms\elements\Toggle;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;

use pocketmine\Player;

use Throwable;
use InvalidArgumentException;

use function strlen;
use function preg_match;

/**
 * @todo remove hardcoded values
 */
class FormFactory {

	public const ID_FORM_LOGIN    = 0;
	public const ID_FORM_REGISTER = 1;

	public const TITLE_FORM_LOGIN    = "";
	public const TITLE_FORM_REGISTER = "";

	public const LABEL_FORM_LOGIN    = Manager::PREFIX_INFO. "Вам необходимо авторизоваться.";
	public const LABEL_FORM_REGISTER = Manager::PREFIX_INFO. "Вам необходимо зарегистрироваться.";

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 *   ___
	 *  / _/ ___  _ ___ _ __
	 * | |_ / _ \| '_| ' `  \
	 * |  _| (_) | | | || || |
	 * |_|  \___/|_| |_||_||_|
	 *
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		$this->manager = $main;
	}

	/**
	 * @param  Player $player
	 * @param  int    $id
	 * @param  string $label
	 *
	 * @throws InvalidArgumentException
	 */
	public function sendForm(Player $player, int $id, string $label): void {
		switch($id) {
			case self::ID_FORM_LOGIN:    $this->sendLoginForm($player, $label);    return;
			case self::ID_FORM_REGISTER: $this->sendRegisterForm($player, $label); return;
		}

		throw new InvalidArgumentException("Invalid form id $id!");
	}

	/**
	 * @param Player $player
	 * @param string $label
	 */
	public function sendLoginForm(Player $player, string $label = self::LABEL_FORM_LOGIN): void {
		// TODO: maybe replace this with a function?
		$player->setImmobile();

		foreach($this->getManager()->getServer()->getOnlinePlayers() as $players){
			$players->hidePlayer($player);
		}

		$main    = $this->getManager();
		$account = $main->getAccount($player->getLowerCaseName(), true);

		if(!isset($account)) {
			$this->sendRegisterForm($player);
			return;
		}

		$list = [
			new Label($label),
			new Input("Пароль:", '')
		];

		if($player->isAuthenticated()) {
			$list[] = new Toggle("Использовать Xbox", $account->hasXuid());
		}

		$player->sendForm(new CustomForm(self::TITLE_FORM_LOGIN, $list,
			function(Player $player, CustomFormResponse $response) use (&$main, &$account): void {
				if($account->getErrorCount() >= Manager::MAX_ERROR_COUNT) {
					$main->saveAccount($account->addAttemptCount());

					$player->kick(Manager::PREFIX_ERROR. "Превышено количество попыток ввода пароля", false);
					return;
				}

				$pass = $response->getInput()->getValue();

				if(empty($pass)) {
					$this->sendLoginForm($player);
					return;
				}

				if(!$account->verifyPassword($pass)) {
					$account->addErrorCount();

					$this->sendLoginForm($player, Manager::PREFIX_ERROR. "Вы ввели неверный пароль.");
					return;
				}

				/**
				 * damn EasyForms
				 */
				try {
					if($response->getToggle()->getValue()) {
						$account->setXuid($player->getXuid());
					}
				} catch(Throwable $exception) {}

				/**
				 * @todo reformat
				 */
				$account->getAddressChain()->add($player->getAddress());

				$event = new AccountLoginEvent($main, $account->setLogined());

				$event->call();

				if($event->isCancelled()) {
					$this->sendLoginForm($player);
					return;
				}

				$main->saveAccount($event->getAccount());

				// TODO: maybe replace this with a function?
				$player->setImmobile(false);

				foreach($this->getManager()->getServer()->getOnlinePlayers() as $players){
					$players->showPlayer($player);
				}

				$player->addTitle(" ", "§aПриятной игры",);
			}
		));
	}

	/**
	 * @param Player $player
	 * @param string $label
	 */
	public function sendRegisterForm(Player $player, string $label = self::LABEL_FORM_REGISTER): void {
		// TODO: maybe replace this with a function?
		$player->setImmobile();

		foreach($this->getManager()->getServer()->getOnlinePlayers() as $players){
			$players->hidePlayer($player);
		}

		$list = [
			new Label($label),
			new Input("Пароль:", ''),
			new Input("Повтор пароля:", '')
		];

		if($player->isAuthenticated()) {
			$list[] = new Toggle("Использовать Xbox");
		}

		$main = $this->getManager();

		$player->sendForm(new CustomForm(self::TITLE_FORM_REGISTER, $list,
			function(Player $player, CustomFormResponse $response) use (&$main): void {
				$pass = $response->getInput()->getValue();

				if(empty($pass)) {
					$this->sendRegisterForm($player);
					return;
				}

				$lenght = Manager::PASSWORD_LENGHT_MIN;

				if(strlen($pass) < $lenght) {
					$this->sendRegisterForm($player, Manager::PREFIX_ERROR. "Минимальная длина пароля §c$lenght §rсимволов.");
					return;
				}

				$lenght = Manager::PASSWORD_LENGHT_MAX;

				if(strlen($pass) > $lenght) {
					$this->sendRegisterForm($player, Manager::PREFIX_ERROR. "Максимальная длина пароля §c$lenght §rсимволов.");
					return;
				}

				if(preg_match(Manager::PASSWORD_REGEXP, $pass)) {
					$this->sendRegisterForm($player, Manager::PREFIX_ERROR. "Разрешены только латинские буквы и цифры.");
					return;
				}

				if($pass !== $response->getInput()->getValue()) {
					$this->sendRegisterForm($player, Manager::PREFIX_ERROR. "Введенные пароли не совпадают.");
					return;
				}

				$use_xuid = false;

				/**
				 * damn EasyForms
				 */
				try {
					$use_xuid = $response->getToggle()->getValue();
				} catch(Throwable $exception) {}

				$account = $main->createAccount($player, $pass, $use_xuid)->setLogined();
				$event   = new AccountRegisterEvent($main, $account);

				$event->call();

				if($event->isCancelled()) {
					$this->sendRegisterForm($player);
					return;
				}

				$main->saveAccount($event->getAccount());

				// TODO: maybe replace this with a function?
				$player->setImmobile(false);

				foreach($this->getManager()->getServer()->getOnlinePlayers() as $players){
					$players->showPlayer($player);
				}

				$player->addTitle(" ", "§aПриятной игры");
			}
		));
	}

	/**
	 * @return Manager
	 */
	private function getManager(): Manager {
		return $this->manager;
	}
}