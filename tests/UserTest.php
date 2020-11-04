<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBBackend;

class UserTest extends TestCase
{
    public function testUnknownUser() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/5f92f0e9fde8b71d307d703b');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testGetAnother() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();
        $this->createUser2();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->user->getId());
        $req->user = $this->user2;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);
    }
}
