<?php declare(strict_types = 1); namespace uni\auth\data\provider;

use uni\auth\task\async\AccountDeleteTask;
use uni\auth\task\async\AccountUpdateTask;

use uni\auth\data\Account;
use uni\auth\data\provider\mysql\AccountBridge;
use uni\auth\data\provider\cache\AccountCache;

use pocketmine\Server;

use function strtolower;

/**
 * @todo reformat all mysql-related stuff
 */
class AccountProvider {

	/**
	 * @var bool
	 */
	private $synchronized;

	/**
	 * @var AccountCache
	 */
	private $cache;

	/**
	 *                       _     _
	 *  _ __  _ _______    _(_) __| | ___ _ __
	 * | '_ \| '_/ _ \ \  / | |/ _' |/ _ \ '_/
	 * | (_) | || (_) \ \/ /| | (_) |  __/ |
	 * | ,__/|_| \___/ \__/ |_|\__,_|\___|_|
	 * |_|
	 *
	 * @param bool $sync
	 */
	public function __construct(bool $sync = false) {
		$this->synchronized = $sync;
		$this->cache        = new AccountCache();

		AccountBridge::createAccountTable();
	}

	public function __destruct() {
		AccountBridge::close();
	}

	/**
	 * @param  string $nick
	 * @param  bool   $ignore_storage
	 *
	 * @return Account|null
	 */
	public function getAccount(string $nick, bool $ignore_storage = false): ?Account {
		$nick    = strtolower($nick);
		$account = $this->getCache()->get($nick);

		if(isset($account)) {
			if($this->isPlayerOnline($nick)) {
				return $account;
			}

			$this->removeAccount($nick, true);
		}

		if($ignore_storage) {
			return null;
		}

		$account = AccountBridge::selectAccount($nick);

		if(!isset($account)) {
			return null;
		}

		$this->setAccount($account, true);
		return $account;
	}

	/**
	 * @param  Account $account
	 * @param  bool    $ignore_storage
	 *
	 * @return AccountProvider
	 */
	public function setAccount(Account $account, bool $ignore_storage = false): AccountProvider {
		if($this->isPlayerOnline($account->getNickname())) {
			$this->getCache()->set($account);
		}

		$this->getCache()->set($account);

		if($ignore_storage) {
			return $this;
		}

		if($this->isSynchronized()) {
			AccountBridge::updateAccount($account);
		} else {
			Server::getInstance()->getAsyncPool()->submitTask(new AccountUpdateTask($account));
		}

		return $this;
	}

	/**
	 * @param  string $nick
	 * @param  bool   $ignore_storage
	 *
	 * @return AccountProvider
	 */
	public function removeAccount(string $nick, bool $ignore_storage = false): AccountProvider {
		$nick = strtolower($nick);

		$this->getCache()->remove($nick);

		if($ignore_storage) {
			return $this;
		}

		if($this->isSynchronized()) {
			AccountBridge::deleteAccount($nick);
		} else {
			Server::getInstance()->getAsyncPool()->submitTask(new AccountDeleteTask($nick));
		}

		return $this;
	}

	/**
	 * @return AccountProvider
	 */
	public function clearStorage(): MysqlProvider {
		AccountBridge::clearAccountTable();

		return $this->clearCache();
	}

	/**
	 * @return AccountProvider
	 */
	public function clearCache(): MysqlProvider {
		$this->cache = new AccountCache();

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	private function isPlayerOnline(string $nick): bool {
		return Server::getInstance()->getPlayerExact($nick) !== null;
	}

	/**
	 * @return bool
	 */
	private function isSynchronized(): bool {
		return $this->synchronized;
	}

	/**
	 * @return AccountCache
	 */
	private function getCache(): AccountCache {
		return $this->cache;
	}
}