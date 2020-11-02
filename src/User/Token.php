<?php

namespace photon\auth\api\MongoDB\User;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;
use photon\http\response\NotFound;

/*
 *  Only users with the admin precondition can perform this action
 */
class Token extends APICommon
{
    protected $handleCORS = true;

    private $user = null;
    private $token = null;

    protected function hookBeforeRequest($request, $match)
    {
        $this->ensureUserIsConnected($request);

        // Find the user
        try {
            $userId = $match[1];
            $query = $this->getUserQueryById($userId);
            $class = $this->getUserClass();
            $this->user = new $class($query);
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Find the token
        try {
            $tokenId = $match[2];
            $query = $this->getTokenQueryById($tokenId);
            $class = $this->getTokenClass();
            $this->token = new $class($query);
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }

        // Allow target user and admin
        if ($this->userIsMe($request, $this->user) === false) {
            if ($this->userIsAdmin($request) === false) {
                throw new \photon\views\APIJson\Exception\Forbidden;
            }
        }
    }

    public function GET($request, $match)
    {
        return $this->token;
    }

    public function DELETE($request, $match)
    {
        $this->token->delete();
        return new NoContent;
    }
}
