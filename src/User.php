<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBUser;
use photon\storage\mongodb;
use photon\http\response\NoContent;

class User extends APICommon
{
    protected $handleCORS = true;

    private $user = null;

    protected function hookBeforeRequest($request, $match) : void
    {
        $this->ensureUserIsConnected($request);

        // Find the user
        try {
            $userId = $match[1];
            $query = $this->getUserQueryById($userId);
            $class = $this->getUserClass();
            $this->user = new $class($query);
        } catch (mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Allow target user and admin
        if ($this->userIsMe($request, $this->user) === false) {
            if ($this->userIsAdmin($request) === false) {
                throw new \photon\views\APIJson\Exception\Forbidden;
            }
        }
    }

    /*
     *  Return the list of all known users
     */
    public function GET($request, $match)
    {
        return $this->user;
    }

    /*
     *  Delete the user
     */
    public function DELETE($request, $match)
    {
        $this->user->delete();
        return new NoContent;
    }
}
