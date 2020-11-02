<?php

namespace photon\auth\api\MongoDB\User;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\Created;
use photon\http\response\BadRequest;

/*
 *  Only users with the admin precondition can perform this action
 */
class Tokens extends APICommon
{
    protected $handleCORS = true;

    private $user = null;

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

        // Allow target user and admin
        if ($this->userIsMe($request, $this->user) === false) {
            if ($this->userIsAdmin($request) === false) {
                throw new \photon\views\APIJson\Exception\Forbidden;
            }
        }
    }

    public function GET($request, $match)
    {
        $query = array(
          'user' => $this->user->getId()
        );
        $tokens = new mongodb\ObjectIterator($this->getTokenClass(), $query);
        return iterator_to_array($tokens);
    }

    public function POST($request, $match)
    {
        if (isset($request->JSON) === false || isset($request->JSON->name) === false) {
            return new BadRequest;
        }

        $token = new $this->config['token_class'];
        $token->setName($request->JSON->name);
        $token->setUser($this->user);
        $token->save();

        $payload = $token->jsonSerialize();
        $payload['token'] = $token->getToken();

        $response = new Created;
        $response->content = json_encode($payload);
        return $response;
    }
}
