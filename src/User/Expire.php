<?php

namespace photon\auth\api\MongoDB\User;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;
use DateTime;

class Expire extends APICommon
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
        }
        catch(mongodb\Exception $e) {
            throw new \photon\views\APIJson\Exception\NotFound;
        }
    }

    public function PUT($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->expire) === false) {
            return new BadRequest;
        }

        $date = new DateTime($request->JSON->expire);
        if ($date === false) {
            return new BadRequest;
        }

        $this->user->setExpirationDate($date);
        $this->user->save();

        return new NoContent;
    }

    /*
     *  Remove the expiration date on an user
     */
    public function DELETE($request, $match)
    {
        $this->user->clearExpirationDate();
        $this->user->save();

        return new NoContent;
    }
}
