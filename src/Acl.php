<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBAcl;
use photon\storage\mongodb;
use photon\http\response\NoContent;

class Acl extends APICommon
{
    protected $handleCORS = true;

    private $acl = null;

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
    }

    /*
     *  Return the list of all known acls
     */
    public function GET($request, $match)
    {
        return $this->acl;
    }

    /*
     *  Delete the acl
     */
    public function DELETE($request, $match)
    {
        $this->acl->delete();
        return new NoContent;
    }
}
