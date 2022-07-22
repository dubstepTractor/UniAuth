<?php declare(strict_types = 1); namespace uni\auth\data\container;

use uni\auth\Manager;

use function explode;
use function implode;
use function in_array;
use function strtolower;
use function array_search;

class AddressChain {

	private const SIZE      = Manager::MAX_ADDRESS_IN_CHAIN;
	private const DELIMITER = '-';

	/**
	 * @param  string $string
	 *
	 * @return AddressChain
	 */
	public static function fromString(string $string): AddressChain {
		return new AddressChain(...explode(self::DELIMITER, $string));
	}

	/**
	 * @var string[]
	 */
	private $list = [];

	/**
	 *                   _        _
	 *   ___  ___  _ __ | |____ _(_)_ __   ___ _ __
	 *  / __\/ _ \| '_ \|  _/ _' | | '_ \ / _ \ '_/
	 * | (__| (_) | | | | || (_) | | | | |  __/ |
	 *  \___/\___/|_| |_|\__\__,_|_|_| |_|\___|_|
	 *
	 *
	 * @param string[] $address
	 */
	public function __construct(string ...$address) {
		$this->add(...$address);
	}

	/**
	 * @return string[]
	 */
	public function getAll(): array {
		return $this->list;
	}

	/**
	 * @param  string[] $address
	 *
	 * @return AddressChain
	 */
	public function add(string ...$address): AddressChain {
		foreach($address as $entry) {
			$entry = trim($entry);

			if(empty($entry)) {
				continue;
			}

			if($this->exists($entry)) {
				continue;
			}

			$this->list[] = $entry;
		}

		$list = $this->getAll();
		$size = self::SIZE;

		if(count($list) > $size) {
			/**
			 * @todo check alternatives
			 */
			$this->list = array_slice($list, -$size, $size);
		}

		return $this;
	}

	/**
	 * @param  string[] $address
	 *
	 * @return AddressChain
	 */
	public function remove(string ...$address): AddressChain {
		foreach($address as $entry) {
			/**
			 * @todo check alternatives
			 */
			$key = array_search($entry, $this->getAll());

			if($key === false) {
				continue;
			}

			unset($this->list[$key]);
		}

		return $this;
	}

	/**
	 * @param  string $address
	 *
	 * @return bool
	 */
	public function exists(string $address): bool {
		/**
		 * @todo check alternatives
		 */
		return in_array($address, $this->getAll());
	}

	/**
	 * @return string
	 */
	public function toString(): string {
		return implode(self::DELIMITER, $this->getAll());
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->toString();
	}
}