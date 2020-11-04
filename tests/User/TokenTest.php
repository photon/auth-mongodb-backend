<?php

namespace tests\User;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBUserToken;

class TokenTest extends \tests\TestCase
{
    public function testReadToken() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $token = new \photon\auth\MongoDBUserToken;
        $token->setName('PHPUnit !!!');
        $token->setUser($this->admin);
        $token->save();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->admin->getId() . '/token/' . $token->getId());
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
    }

    public function testUnknownToken() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->admin->getId() . '/token/5f92f0e9fde8b71d307d703b');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testUnknownUser() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/5f92f0e9fde0000d307d703b/token/5f92f0e9fde8b71d307d703b');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testForbiddenAccess() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        $token = new \photon\auth\MongoDBUserToken;
        $token->setName('PHPUnit !!!');
        $token->setUser($this->admin);
        $token->save();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->admin->getId() . '/token/' . $token->getId());
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);
    }
}
