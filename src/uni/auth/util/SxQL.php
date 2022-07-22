<?php declare(strict_types = 1); namespace uni\auth\util;

use uni\auth\Manager;

use pocketmine\utils\MainLogger;

use mysqli_result;
use mysqli;

use function time;
use function mysqli_ping;
use function mysqli_init;
use function mysqli_query;
use function mysqli_error;
use function mysqli_close;
use function mysqli_options;
use function mysqli_real_connect;
use function mysqli_connect_error;

use const MYSQLI_OPT_CONNECT_TIMEOUT;

/**
 * @todo no more exceptions in production!
 * @todo check thread-safe alternatives
 * @todo reformat all mysql-related stuff
 */
use Exception;

class SxQL {

	private const CONNECT_TIMEOUT  = 2;    // sec.
	private const CONNECT_LIFETIME = 3600; // sec.

	/**
	 * @var mysqli
	 */
	private static $link;

	/**
	 * @var int
	 */
	private static $link_timestamp;

	/**
	 *                          _
	 *  _ __ ___    ______ __ _| |
	 * | '  ' \ \  / / __// _' | |
	 * | || || \ \/ /\__ \ (_) | |
	 * |_||_||_|\  / /___/\__, |_|
	 *         /__/          |_|
	 *
	 * @return mysqli
	 *
	 * @throws Exception
	 */
	public static function connect(): mysqli {
		/**
		 * @todo reformat?
		 */
		if(isset(self::$link) and isset(self::$link_timestamp)){
			if(time() - self::$link_timestamp < self::CONNECT_LIFETIME) {
				return self::$link;
			}

			if(mysqli_ping(self::$link)) {
				/**
				 * let's keep going!
				 */
				self::$link_timestamp = time();

				return self::$link;
			}

			MainLogger::getLogger()->debug("SxQL::connect() - MySQL server has gone away, reconnecting...");
		}

		self::close();

		/**
		 * start connecting...
		 */
		$link  = mysqli_init();
		$param = [
			Manager::MYSQL_HOSTNAME,
			Manager::MYSQL_USERNAME,
			Manager::MYSQL_PASSWORD,
			Manager::MYSQL_DATABASE
		];

		mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, self::CONNECT_TIMEOUT);

		if(!mysqli_real_connect($link, ...$param)) {
			mysqli_close($link);

			/**
			MainLogger::getLogger()->emergency('SxQL::connect() - '. mysqli_connect_error());
			return null;
			 */

			throw new Exception(mysqli_connect_error());
		}

		self::$link           = $link;
		self::$link_timestamp = time();

		return $link;
	}

	/**
	 * @return bool
	 */
	public static function close(): bool {
		if(!isset(self::$link)){
			return true;
		}

		$result = mysqli_close(self::$link);

		self::$link           = null;
		self::$link_timestamp = null;

		return $result;
	}

	/**
	 * @param  string $sql
	 *
	 * @return mysqli_result|bool
	 *
	 * @throws Exception
	 */
	public static function query(string $sql) {
		$link = self::connect();

		/**
		if(!isset($link)) {
			return false;
		}*/

		$result = mysqli_query($link, $sql);

		if(mysqli_error($link)) {
			/**
			MainLogger::getLogger()->emergency('SxQL::query() - '. mysqli_error($link));
			return false;
			 */

			throw new Exception(mysqli_error($link));
		}

		return $result;
	}
}