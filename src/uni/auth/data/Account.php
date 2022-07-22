<?php declare(strict_types = 1); namespace uni\auth\data;

use uni\auth\data\container\AddressChain;

use pocketmine\Player;

use function time;
use function strval;
use function intval;
use function strtolower;
use function password_hash;
use function password_verify;

use const PASSWORD_DEFAULT;

class Account {

	public const INDEX_NICKNAME        = 'nickname';
	public const INDEX_PASSWORD        = 'password';
	public const INDEX_ADDRESS_CHAIN   = 'address_chain';
	public const INDEX_XUID            = 'xuid';
	public const INDEX_ATTEMPT_COUNT   = 'attempt_count';
	public const INDEX_LOGIN_TIMESTAMP = 'login_timestamp';

	public const XUID_UNDEFINED = '';

	/**
	 * @todo   validate data types
	 *
	 * @param  string  $nick
	 * @param  mixed[] $data
	 *
	 * @return Account
	 */
	public static function fromDataEntry(string $nick, array $data): Account {
		$data[self::INDEX_NICKNAME] = $nick;

		return self::fromData($data);
	}

	/**
	 * @todo   validate data types
	 *
	 * @param  mixed[] $data
	 *
	 * @return Account
	 *
	 * @throws Exception
	 */
	public static function fromData(array $data): Account {
		if(!isset($data[self::INDEX_NICKNAME])) {
			throw new Exception("Account::fromData() - Index INDEX_NICKNAME does not exists!");
		}

		$nick = strval($data[self::INDEX_NICKNAME]);

		if(!isset($data[self::INDEX_PASSWORD])) {
			throw new Exception("Account::fromData() - Index INDEX_PASSWORD does not exists!");
		}

		$pass = strval($data[self::INDEX_PASSWORD]);

		if(!isset($data[self::INDEX_ADDRESS_CHAIN])) {
			throw new Exception("Account::fromData() - Index INDEX_ADDRESS_CHAIN does not exists!");
		}

		$chain = AddressChain::fromString(strval($data[self::INDEX_ADDRESS_CHAIN]));

		if(!isset($data[self::INDEX_XUID])) {
			throw new Exception("Account::fromData() - Index INDEX_XUID does not exists!");
		}

		$xuid = strval($data[self::INDEX_XUID]);

		if(!isset($data[self::INDEX_ATTEMPT_COUNT])) {
			throw new Exception("Account::fromData() - Index INDEX_ATTEMPT_COUNT does not exists!");
		}

		$count = intval($data[self::INDEX_ATTEMPT_COUNT]);

		if(!isset($data[self::INDEX_LOGIN_TIMESTAMP])) {
			throw new Exception("Account::fromData() - Index INDEX_LOGIN_TIMESTAMP does not exists!");
		}

		$time = intval($data[self::INDEX_LOGIN_TIMESTAMP]);

		return new Account($nick, $pass, $chain, $xuid, $count, $time);
	}

	/**
	 * @param  Player $player
	 * @param  string $pass
	 * @param  bool   $use_xuid
	 *
	 * @return Account
	 */
	public static function create(Player $player, string $pass, bool $use_xuid = false): Account {
		$xuid = self::XUID_UNDEFINED;

		if($use_xuid and $player->isAuthenticated()) {
			$xuid = $player->getXuid();
		}

		return new Account(
			$player->getLowerCaseName(),
			password_hash($pass, PASSWORD_DEFAULT),
			new AddressChain($player->getAddress()),
			$xuid,
			0,
			time()
		);
	}

	/**
	 * ACTUAL DATA
	 *
	 * @var string
	 */
	private $nickname;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var AddressChain
	 */
	private $address_chain;

	/**
	 * @var string
	 */
	private $xuid;

	/**
	 * @var int
	 */
	private $attempt_count;

	/**
	 * @var int
	 */
	private $login_timestamp;

	/**
	 * TEMPORARY PARAMETERS
	 *
	 * @var bool
	 */
	private $logined;

	/**
	 * @var int
	 */
	private $error_count;

	/**
	 * @var int
	 */
	private $join_timestamp;

	/**
	 * @var bool
	 */
	private $protected;

	/**
	 *                                    _
	 *   __ _  ___  ___  ___  _   _ _ __ | |__
	 *  / _' |/ __\/ __\/ _ \| | | | '_ \|  _/
	 * | (_) | (__| (__| (_) | |_| | | | | |_
	 *  \__,_|\___/\___/\___/ \___/|_| |_|\__\
	 *
	 *
	 * @param string       $nick
	 * @param string       $pass
	 * @param AddressChain $chain
	 * @param string       $xuid
	 * @param int          $count
	 * @param int          $time
	 */
	public function __construct(string $nick, string $pass, AddressChain $chain, string $xuid, int $count, int $time) {
		$this->nickname        = strtolower($nick);
		$this->password        = $pass;
		$this->address_chain   = $chain;
		$this->xuid            = $xuid;
		$this->attempt_count   = $count;
		$this->login_timestamp = $time;

		$this->logined        = false;
		$this->error_count    = 0;
		$this->join_timestamp = time();
		$this->protected      = false;
	}

	/**
	 * @return string
	 */
	public function getNickname(): string {
		return $this->nickname;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * @param  string $pass
	 * @param  bool   $need_hash
	 *
	 * @return Account
	 */
	public function setPassword(string $pass, bool $need_hash = true): Account {
		$this->password = $need_hash ? password_hash($pass, PASSWORD_DEFAULT) : $pass;

		return $this;
	}

	/**
	 * @param  string $pass
	 *
	 * @return bool
	 */
	public function verifyPassword(string $pass): bool {
		return password_verify($pass, $this->getPassword());
	}

	/**
	 * @return AddressChain
	 */
	public function getAddressChain(): AddressChain {
		return $this->address_chain;
	}

	/**
	 * @param  string $address
	 *
	 * @return bool
	 */
	public function verifyAddress(string $address): bool {
		return $this->getAddressChain()->exists($address);
	}

	/**
	 * @return string
	 */
	public function getXuid(): string {
		return $this->xuid;
	}

	/**
	 * @param  string $xuid
	 *
	 * @return Account
	 */
	public function setXuid(string $xuid = self::XUID_UNDEFINED): Account {
		$this->xuid = $xuid;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasXuid(): bool {
		return $this->getXuid() !== self::XUID_UNDEFINED;
	}

	/**
	 * @param  string $xuid
	 *
	 * @return bool
	 */
	public function verifyXuid(string $xuid): bool {
		return $this->getXuid() === $xuid;
	}

	/**
	 * @return int
	 */
	public function getAttemptCount(): int {
		return $this->attempt_count;
	}

	/**
	 * @param  int $count
	 *
	 * @return Account
	 */
	public function addAttemptCount(int $count = 1): Account {
		$this->attempt_count += $count;

		return $this;
	}

	/**
	 * @return Account
	 */
	public function resetAttemptCount(): Account {
		$this->attempt_count = 0;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLoginTimestamp(): int {
		return $this->login_timestamp;
	}

	/**
	 * @param  int $time
	 *
	 * @return Account
	 */
	public function setLoginTimestamp(int $time): Account {
		$this->login_timestamp = $time;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isLogined(): bool {
		return $this->logined;
	}

	/**
	 * @param  bool $status
	 *
	 * @return Account
	 */
	public function setLogined(bool $status = true): Account {
		$this->logined = $status;

		if($status) {
			$this->resetErrorCount()->resetAttemptCount()->setLoginTimestamp(time());
		}

		return $this;
	}

	/**
	 * @return int
	 */
	public function getErrorCount(): int {
		return $this->error_count;
	}

	/**
	 * @param  int $count
	 *
	 * @return Account
	 */
	public function addErrorCount(int $count = 1): Account {
		$this->error_count += $count;

		return $this;
	}

	/**
	 * @return Account
	 */
	public function resetErrorCount(): Account {
		$this->error_count = 0;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getJoinTimestamp(): int {
		return $this->join_timestamp;
	}

	/**
	 * @param  int $time
	 *
	 * @return Account
	 */
	public function setJoinTimestamp(int $time): Account {
		$this->join_timestamp = $time;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isProtected(): bool {
		return $this->protected;
	}

	/**
	 * prevent account from being removed from cache due to player quit.
	 *
	 * @param  bool $value
	 *
	 * @return Account
	 */
	public function setProtected(bool $value = true): Account {
		$this->protected = $value;

		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function toDataEntry(): array {
		return [
			self::INDEX_PASSWORD        => $this->getPassword(),
			self::INDEX_ADDRESS_CHAIN   => $this->getAddressChain()->toString(),
			self::INDEX_XUID            => $this->getXuid(),
			self::INDEX_ATTEMPT_COUNT   => $this->getAttemptCount(),
			self::INDEX_LOGIN_TIMESTAMP => $this->getLoginTimestamp()
		];
	}

	/**
	 * @return mixed[]
	 */
	public function toData(): array {
		$data = [
			self::INDEX_NICKNAME => $this->getNickname()
		];

		return $data + $this->toDataEntry();
	}
}