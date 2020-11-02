<?php

namespace tests\User;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class BlockTest extends \tests\TestCase
{
    public function testUnknownUser()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $req = \photon\test\HTTP::baseRequest('PUT', '/api/user/5f92f0e9fde8b71d307d703b/block');
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testBlock()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Freshly created user is not blocked
        $isBlocked = $this->user->isBlocked();
        $this->assertEquals(false, $isBlocked);

        // An admin block the user
        $req = \photon\test\HTTP::baseRequest('PUT', '/api/user/' . $this->user->getId() . '/block');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(204, $resp->status_code);

        // User is blocked
        $this->user->reload();
        $isBlocked = $this->user->isBlocked();
        $this->assertEquals(true, $isBlocked);
    }

    public function testUnblock()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Block the user
        $this->user->block();
        $this->user->save();
        $this->user->reload();
        $isBlocked = $this->user->isBlocked();
        $this->assertEquals(true, $isBlocked);

        // An admin unblock the user
        $req = \photon\test\HTTP::baseRequest('DELETE', '/api/user/' . $this->user->getId() . '/block');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(204, $resp->status_code);

        // User is unblocked
        $this->user->reload();
        $isBlocked = $this->user->isBlocked();
        $this->assertEquals(false, $isBlocked);
    }
}
