<?php

namespace photon\auth\api\MongoDB\User;

use photon\auth\api\MongoDB\APICommon;
use photon\storage\mongodb;
use photon\http\response\NoContent;
use photon\http\response\BadRequest;

/*
 *  Update the user password
 *  - Regular user can update only himself
 *  - Admin can force a password for a user
 */
class Password extends APICommon
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
    }

    /*
     *  Update the user password
     *  - Regular user can update only himself
     *  - Admin can force a password for a user
     */
    public function PUT($request, $match)
    {
      if ($this->userIsMe($request, $this->user) === false) {
          if ($this->userIsAdmin($request) === false) {
              throw new \photon\views\APIJson\Exception\Forbidden;
          }
      }

      if (isset($request->JSON) === false || isset($request->JSON->password) === false) {
          return new BadRequest;
      }

      $password = $request->JSON->password;
      // TODO: Hook password strength ?

      $this->user->setPassword($password);
      $this->user->save();

      return new NoContent;
    }

    /*
     *  Delete the user password
     *  Force him to use a password recover method
     */
     public function DELETE($request, $match)
     {
       $this->ensureUserIsAdmin($request);

       $this->user->clearPassword();
       $this->user->save();

       return new NoContent;
     }
}
