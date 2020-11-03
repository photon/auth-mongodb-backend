<?php

namespace photon\auth\api\MongoDB\Acl;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;

/*
 *  Only users with the admin precondition can perform this action
 */
class Group extends APICommon
{
    protected $handleCORS = true;

    private $acl = null;
    private $group = null;

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
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Find the group
        try {
            $groupId = $match[2];
            $query = $this->getGroupQueryById($groupId);
            $class = $this->getGroupClass();
            $this->group = new $class($query);
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    public function DELETE($request, $match)
    {
        $this->acl->removeGroup($this->group);
        $this->acl->save();

        return new NoContent;
    }
}
