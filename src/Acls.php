<?php

namespace photon\auth\api\MongoDB;

use photon\auth\MongoDBUser;
use photon\storage\mongodb;
use photon\http\response\BadRequest;
use photon\http\response\Created;

class Acls extends APICommon
{
    protected $handleCORS = true;

    protected function hookBeforeRequest($request, $match)
    {
        $this->ensureUserIsConnected($request);
        $this->ensureUserIsAdmin($request);
    }

    /*
     *  Return the list of all known users
     */
    public function GET($request, $match)
    {
        $it = new mongodb\ObjectIterator($this->getAclClass());
        return iterator_to_array($it);
    }

    /*
     *  Create a new user
     */
    public function POST($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->name) === false) {
            return new BadRequest;
        }

        $class = $this->getAclClass();
        $acl = new $class;
        $acl->setName($request->JSON->name);
        $acl->save();

        $response = new Created;
        $response->content = json_encode($acl);
        return $response;
    }
}
