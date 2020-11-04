<?php

namespace photon\auth\api\MongoDB\Acl;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\BadRequest;
use photon\http\response\Created;

class Groups extends APICommon
{
    protected $handleCORS = true;

    private $acl = null;

    protected function hookBeforeRequest($request, $match) : void
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
    }

    /*
     *  Add group in the acl
     */
    public function POST($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->group) === false) {
            return new BadRequest;
        }

      // Append one group in the acl
        try {
            $class = $this->getGroupClass();
            $query = $this->getGroupQueryById($request->JSON->group);
            $group = new $class($query);
        } catch (mongodb\Exception $e) {
            return new BadRequest;
        }

        $this->acl->addGroup($group);
        $this->acl->save();

        return new Created;
    }
}
