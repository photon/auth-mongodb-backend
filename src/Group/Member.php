<?php

namespace photon\auth\api\MongoDB\Group;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;

/*
 *  Only users with the admin precondition can perform this action
 */
class Member extends APICommon
{
    protected $handleCORS = true;

    private $group = null;
    private $user = null;

    protected function hookBeforeRequest($request, $match)
    {
        $this->ensureUserIsConnected($request);
        $this->ensureUserIsAdmin($request);

        // Find the group
        try {
            $groupId = $match[1];
            $query = $this->getGroupQueryById($groupId);
            $class = $this->getGroupClass();
            $this->group = new $class($query);
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Find the user
        try {
            $userId = $match[2];
            $query = $this->getUserQueryById($userId);
            $class = $this->getUserClass();
            $this->user = new $class($query);
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    public function DELETE($request, $match)
    {
        $this->group->removeUser($this->user);
        $this->group->save();

        return new NoContent;
    }
}
