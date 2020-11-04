<?php

namespace tests\User;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBUserToken;

class TokensTest extends \tests\TestCase
{
    public function testUnknownUser() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/5f92f0e00000000d307d703b/token');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testListToken() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

      // List user tokens (connected as user)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->user->getId() . '/token');
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(0, count($json));

      // Create a token
        $token = new MongoDBUserToken;
        $token->setName('phpunit');
        $token->setUser($this->user);
        $token->save();

      // List user tokens (connected as user)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->user->getId() . '/token');
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(1, count($json));

      // List another user tokens (connected as admin)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->admin->getId() . '/token');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(0, count($json));
    }

    public function testListTokenOfAnother() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

      // List user tokens (connected as user)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $this->admin->getId() . '/token');
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);
    }

    public function testCreateToken() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

      // Ensure user do not have token
        $count = MongoDBUserToken::count(array(
        'user' => $this->user->getId()
        ));
        static::assertEquals(0, $count);

      // Create a token
        $payload = array(
        'name' => 'phpunit'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/token';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(201, $resp->status_code);

      // Ensure one token is created for this user
        $count = MongoDBUserToken::count(array(
        'user' => $this->user->getId()
        ));
        static::assertEquals(1, $count);
    }

    public function testDeleteToken() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

      // Create a token for the user
        $token = new \photon\auth\MongoDBUserToken;
        $token->setName('phpunit');
        $token->setUser($this->user);
        $token->save();
        $tokenId = $token->getId();

      // Ensure one token is created for this user
        $count = MongoDBUserToken::count(array(
        'user' => $this->user->getId()
        ));
        static::assertEquals(1, $count);

      // Delete it
        $req = \photon\test\HTTP::baseRequest('DELETE', '/api/user/' . $this->user->getId() . '/token/' . $tokenId);
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);

      // Ensure zero token is created for this user
        $count = MongoDBUserToken::count(array(
        'user' => $this->user->getId()
        ));
        static::assertEquals(0, $count);
    }

    public function testUserTokensBadPayload() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

      // No expire field
        $payload = array(
        'my' => 'bad'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/token';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(400, $resp->status_code);
    }
}
