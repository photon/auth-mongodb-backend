<?php

namespace photon\auth\api\MongoDB\Acl;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\BadRequest;
use photon\http\response\Created;

/*
 *  Only users with the admin precondition can perform this action
 */
class Members extends APICommon
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
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    /*
     *  Add users in the acl
     */
    public function POST($request, $match)
    {
      if (isset($request->JSON) === false || isset($request->JSON->member) === false) {
          return new BadRequest;
      }

      // Append one user in the acl
      try {
          $class = $this->getUserClass();
          $query = $this->getUserQueryById($request->JSON->member);
          $user = new $class($query);
      }
      catch(mongodb\Exception $e) {
        return new BadRequest;
      }

      $this->acl->addUser($user);
      $this->acl->save();

      return new Created;
    }
}
