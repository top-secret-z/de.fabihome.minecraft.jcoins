<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserList;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * Worker for JCoins Minecraft verification.
 *
 * @author          Fabian Graf
 * @copyright       2017 - 2021 Fabian Graf
 * @license         Fabihome Free License <https://https://fabihome.de/license.html>
 * @package         de.fabihome.minecraft.jcoins
 */
class JCoinsMinecraftVerifyRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = UserList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('user_table.isVerified = ?', [1]);
		$this->objectList->sqlOrderBy = 'user_table.userID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('de.wcflabs.jcoins.statement.object', 'de.fabihome.minecraft.jcoins.verify');
		$objectTypeID = $objectType->objectTypeID;
		
		foreach ($this->objectList as $user) {
			$sql = "SELECT	COUNT(*) AS counter
					FROM	wcf".WCF_N."_jcoins_statement
					WHERE	userID = ? AND objectTypeID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$user->userID, $objectTypeID]);
			$count = $statement->fetchColumn();
			
			if (!$count) {
				UserJCoinsStatementHandler::getInstance()->create('de.fabihome.minecraft.jcoins.verify', $user);
			}
		}
	}
}
