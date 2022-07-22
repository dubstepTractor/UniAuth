<?php declare(strict_types = 1); namespace uni\auth\event\account;

use uni\auth\event\account\AccountEvent;

class AccountResetEvent extends AccountEvent {

	public static $handlerList = null;
}