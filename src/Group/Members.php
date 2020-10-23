<?php

namespace photon\auth\api\MongoDB\Group;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;
use photon\http\response\Created;

/*
 *  Only users with the admin precondition can perform this action
 */
class Members extends APICommon
{
    protected $handleCORS = true;

    private $group = null;

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
    }

    /*
     *  Add users in the group
     */
    public function POST($request, $match)
    {
      if (isset($request->JSON) === false) {
          return new BadRequest;
      }

      // Append one user in the group
      if (isset($request->JSON->member)) {
        $class = $this->getUserClass();
        $user = new $class($this->getUserQueryById($request->JSON->member));

        $this->group->addUser($user);
        $this->group->save();

        return new Created;
      }

      return new BadRequest;
    }
}
