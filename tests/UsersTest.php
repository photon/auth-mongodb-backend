<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBBackend;

class UsersTest extends TestCase
{
    public function testListUsers() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        // List users on an empty database (not connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user');
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);

        // Create an user
        $user1 = new \photon\auth\MongoDBUser;
        $user1->setName('John DOE');
        $user1->setLogin('jd@exemple.com');
        $user1->setPassword('strong');
        $user1->save();

        // List users (connected as regular user)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user');
        $req->user = $user1;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);

        // Grant admin role to the user
        $acl = new \photon\auth\MongoDBAcl(array('name' => 'admin-users'));
        $acl->addUser($user1);
        $acl->save();

        // List users (connected as admin)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user');
        $req->user = $user1;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(1, count($json));

        // Create a second user
        $user2 = new \photon\auth\MongoDBUser;
        $user2->setName('Mick Robot');
        $user2->setLogin('mr@exemple.com');
        $user2->setPassword('secret');
        $user2->save();

        // List users (connected as admin)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user');
        $req->user = $user1;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(2, count($json));
    }


    public function testCreateDeleteUser() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

      // Create the user 'Joe'
        $payload = array(
        'login' => 'Joe',
        'password' => 'foobar',
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(201, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);

      // Reload user (generate exception if do not exists)
        $config = MongoDBBackend::getConfig();
        $user = new \photon\auth\MongoDBUser(array($config['user_login'] => 'Joe'));

      // Get the user
        $req = \photon\test\HTTP::baseRequest('GET', '/api/user/' . $user->getId());
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);

      // Delete the user
        $req = \photon\test\HTTP::baseRequest('DELETE', '/api/user/' . $user->getId());
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);
    }

    public function testCreateUserBadPayload() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $payload = array(
        'bad' => 'name'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(400, $resp->status_code);
    }
}
