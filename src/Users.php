<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBUser;
use photon\storage\mongodb;
use photon\http\response\BadRequest;
use photon\http\response\Created;

class Users extends APICommon
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
        $users = new mongodb\ObjectIterator($this->getUserClass());
        return iterator_to_array($users);
    }

    /*
     *  Create a new user
     */
    public function POST($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->login) === false || isset($request->JSON->password) === false) {
            return new BadRequest;
        }

        $class = $this->getUserClass();
        $user = new $class;
        $user->setLogin($request->JSON->login);
        $user->setPassword($request->JSON->password);
        $user->save();

        $response = new Created;
        $response->content = json_encode($user);
        return $response;
    }
}
