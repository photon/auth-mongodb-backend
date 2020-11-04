<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBUser;
use photon\storage\mongodb;
use photon\http\response\BadRequest;
use photon\http\response\Created;

class Groups extends APICommon
{
    protected $handleCORS = true;

    protected function hookBeforeRequest($request, $match) : void
    {
        $this->ensureUserIsConnected($request);
        $this->ensureUserIsAdmin($request);
    }

    /*
     *  Return the list of all known users
     */
    public function GET($request, $match)
    {
        $users = new mongodb\ObjectIterator($this->getGroupClass());
        return iterator_to_array($users);
    }

    /*
     *  Create a new user
     */
    public function POST($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->name) === false) {
            return new BadRequest;
        }

        $class = $this->getGroupClass();
        $group = new $class;
        $group->setName($request->JSON->name);
        $group->save();

        $response = new Created;
        $response->content = json_encode($group);
        return $response;
    }
}
