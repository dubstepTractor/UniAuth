<?php declare(strict_types = 1); namespace uni\auth\data\provider\mysql;

use uni\auth\util\SxQL;

use uni\auth\data\Account;
use uni\auth\data\container\AddressChain;

use mysqli_stmt;

use function strtolower;
use function str_replace;
use function mysqli_fetch_assoc;
use function mysqli_free_result;
use function mysqli_num_rows;

/**
 * @todo remove hardcoded values
 * @todo reformat all mysql-related stuff
 */
class AccountBridge extends SxQL {

	private const QUERY_SELECT = "SELECT * FROM `uniauth_account` WHERE `nickname` = ':nickname' LIMIT 1";
	private const QUERY_UPDATE = "UPDATE `uniauth_account` SET `password` = ':password', `address_chain` = ':address_chain', `xuid` = ':xuid', `attempt_count` = ':attempt_count', `login_timestamp` = ':login_timestamp' WHERE `nickname` = ':nickname'";
	private const QUERY_INSERT = "INSERT INTO `uniauth_account` (`nickname`, `password`, `address_chain`, `xuid`, `attempt_count`, `login_timestamp`) VALUES (':nickname', ':password', ':address_chain', ':xuid', ':attempt_count', ':login_timestamp')";
	private const QUERY_DELETE = "DELETE FROM `uniauth_account` WHERE `nickname` = ':nickname'";
	private const QUERY_TABLE_CREATE = "CREATE TABLE IF NOT EXISTS `uniauth_account` (`id` INT(16) PRIMARY KEY NOT NULL AUTO_INCREMENT, `nickname` VARCHAR(16) UNIQUE KEY NOT NULL, `password` VARCHAR(128) NOT NULL, `address_chain` VARCHAR(64) NOT NULL, `xuid` VARCHAR(64) NOT NULL, `attempt_count` SMALLINT(4) NOT NULL, `login_timestamp` INT(32) NOT NULL)";
	private const QUERY_TABLE_CLEAR  = "DELETE FROM `uniauth_account`";

	/**
	 *                                    _     _         _     _
	 *   __ _  ___  ___  ___  _   _ _ __ | |__ | |__  _ _(_) __| | __ _  ___
	 *  / _' |/ __\/ __\/ _ \| | | | '_ \|  _/ | '_ \| '_| |/ _' |/ _' |/ _ \
	 * | (_) | (__| (__| (_) | |_| | | | | |_  | (_) | | | | (_) | (_) |  __/
	 *  \__,_|\___/\___/\___/ \___/|_| |_|\__\ |_,__/|_| |_|\__,_|\__, |\___/
	 *                                                            /___/
	 *
	 * @param  string $nick
	 *
	 * @return Account|null
	 */
	public static function selectAccount(string $nick): ?Account {
		$nick = strtolower($nick);
		$sql  = self::buildAccountQuery(self::QUERY_SELECT, $nick);

		$result = self::query($sql);
		$data   = mysqli_fetch_assoc($result);

		mysqli_free_result($result);

		if(!isset($data)) {
			return null;
		}

		return Account::fromData($data);
	}

	/**
	 * @param  Account $account
	 *
	 * @return bool
	 */
	public static function updateAccount(Account $account): bool {
		$nick = $account->getNickname();
		$sql  = self::isAccountExists($nick) ? self::QUERY_UPDATE : self::QUERY_INSERT;
		$sql  = self::buildAccountQuery($sql, $nick, $account);

		return self::query($sql);
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public static function deleteAccount(string $nick): bool {
		$nick = strtolower($nick);
		$sql  = self::buildAccountQuery(self::QUERY_DELETE, $nick);

		return self::query($sql);
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public static function isAccountExists(string $nick): bool {
		$nick = strtolower($nick);

		$sql    = self::buildAccountQuery(self::QUERY_SELECT, $nick);
		$result = self::query($sql);
		$count  = mysqli_num_rows($result);

		mysqli_free_result($result);

		return $count > 0;
	}

	/**
	 * @return bool
	 */
	public static function createAccountTable(): bool {
		return self::query(self::QUERY_TABLE_CREATE);
	}

	/**
	 * @return bool
	 */
	public static function clearAccountTable(): bool {
		return self::query(self::QUERY_TABLE_CLEAR);
	}

	/**
	 * @todo optimize ASAP
	 *
	 * @param  string  $sql
	 * @param  string  $nick
	 * @param  Account $account
	 *
	 * @return string
	 */
	private static function buildAccountQuery(string $sql, string $nick, Account $account = null): string {
		$sql = str_replace(':nickname', strtolower($nick), $sql);

		if(isset($account)) {
			foreach($account->toDataEntry() as $index => $value) {
				$sql = str_replace(":$index", $value, $sql);
			}
		}

		return $sql;
	}
}