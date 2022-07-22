<?php declare(strict_types = 1); namespace uni\auth\data\provider\cache;

use uni\auth\data\Account;

use function strtolower;

class AccountCache {

	/**
	 * @var Account[]
	 */
	private $list = [];

	/**
	 *                  _
	 *   ___  __ _  ___| |__   ___
	 *  / __\/ _' |/ __| '_ \ / _ \
	 * | (__| (_) | (__| | | |  __/
	 *  \___/\__,_|\___|_| |_|\___/
	 *
	 *
	 * @param Account[] $list
	 */
	public function __construct(Account ...$list) {
		foreach($list as $account) {
			$this->set($account);
		}
	}

	/**
	 * @return Account[]
	 */
	public function getAll(): array {
		return $this->list;
	}

	/**
	 * @param  string $nick
	 *
	 * @return Account|null
	 */
	public function get(string $nick): ?Account {
		$nick = strtolower($nick);

		if(!isset($this->list[$nick])) {
			return null;
		}

		return $this->list[$nick];
	}

	/**
	 * @param  Account $account
	 *
	 * @return AccountCache
	 */
	public function set(Account $account): AccountCache {
		$this->list[$account->getNickname()] = $account;

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return AccountCache
	 */
	public function remove(string $nick): AccountCache {
		$nick = strtolower($nick);

		if(isset($this->list[$nick])) {
			unset($this->list[$nick]);
		}

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public function exists(string $nick): bool {
		return isset($this->list[strtolower($nick)]);
	}
}