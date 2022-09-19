<?php
namespace wcf\system\event\listener;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * JCoins listener for Minecraft verification.
 *
 * @author          Fabian Graf
 * @copyright       2017 - 2021 Fabian Graf
 * @license         Fabihome Free License <https://https://fabihome.de/license.html>
 * @package         de.fabihome.minecraft.jcoins
 */
class JCoinsMinecraftVerifyListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;
		
		if ($eventObj->getActionName() == 'update') {
			// get users, parameters and object type
			$objects = $eventObj->getObjects();
			$params = $eventObj->getParameters();
			
			if (!isset($params['data']['isVerified'])) return;
			if (!$params['data']['isVerified']) return;

			// update users
			foreach ($objects as $user) {
				// assign JCoins
				UserJCoinsStatementHandler::getInstance()->create('de.fabihome.minecraft.jcoins.verify', $user->getDecoratedObject());
			}
		}
	}
}
