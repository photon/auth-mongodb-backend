<?php

namespace photon\auth\api\MongoDB\Acl;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;

/*
 *  Only users with the admin precondition can perform this action
 */
class Member extends APICommon
{
    protected $handleCORS = true;

    private $acl = null;
    private $user = null;

    protected function hookBeforeRequest($request, $match)
    {
        $this->ensureUserIsConnected($request);
        $this->ensureUserIsAdmin($request);

        // Find the acl
        try {
            $aclId = $match[1];
            $query = $this->getAclQueryById($aclId);
            $class = $this->getAclClass();
            $this->acl = new $class($query);
        } catch (mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Find the user
        try {
            $userId = $match[2];
            $query = $this->getUserQueryById($userId);
            $class = $this->getUserClass();
            $this->user = new $class($query);
        } catch (mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    public function DELETE($request, $match)
    {
        $this->acl->removeUser($this->user);
        $this->acl->save();

        return new NoContent;
    }
}
