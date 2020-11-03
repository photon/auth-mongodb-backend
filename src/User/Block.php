<?php

namespace photon\auth\api\MongoDB\User;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;

/*
 *  Only users with the admin precondition can perform this action
 */
class Block extends APICommon
{
    protected $handleCORS = true;

    private $user = null;

    protected function hookBeforeRequest($request, $match)
    {
        $this->ensureUserIsConnected($request);
        $this->ensureUserIsAdmin($request);

        // Find the user
        try {
            $userId = $match[1];
            $query = $this->getUserQueryById($userId);
            $class = $this->getUserClass();
            $this->user = new $class($query);
        } catch (mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    /*
     *  Block an user, he will not be able to login again
     */
    public function PUT($request, $match)
    {
        $this->user->block();
        $this->user->save();

        return new NoContent;
    }

    /*
     *  Unlock an user, he will be able to login again
     */
    public function DELETE($request, $match)
    {
        $this->user->unblock();
        $this->user->save();

        return new NoContent;
    }
}
