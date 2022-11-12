<?php

/*
 * Copyright by Fabi_995.
 * Modified by SoftCreatR.dev.
 *
 * License: https://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace wcf\system\worker;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserList;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * Worker for JCoins Minecraft verification.
 */
class JCoinsMinecraftVerifyRebuildDataWorker extends AbstractRebuildDataWorker
{
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
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add('user_table.isVerified = ?', [1]);
        $this->objectList->sqlOrderBy = 'user_table.userID';
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('de.wcflabs.jcoins.statement.object', 'de.fabihome.minecraft.jcoins.verify');
        $objectTypeID = $objectType->objectTypeID;

        foreach ($this->objectList as $user) {
            $sql = "SELECT    COUNT(*) AS counter
                    FROM    wcf" . WCF_N . "_jcoins_statement
                    WHERE    userID = ? AND objectTypeID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$user->userID, $objectTypeID]);
            $count = $statement->fetchColumn();

            if (!$count) {
                UserJCoinsStatementHandler::getInstance()->create('de.fabihome.minecraft.jcoins.verify', $user);
            }
        }
    }
}
