<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBGroup;
use photon\storage\mongodb;
use photon\http\response\NoContent;

class Group extends APICommon
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
     *  Return the list of all known groups
     */
    public function GET($request, $match)
    {
        return $this->group;
    }

    /*
     *  Delete the group
     */
     public function DELETE($request, $match)
     {
         $this->group->delete();
         return new NoContent;
     }
}
