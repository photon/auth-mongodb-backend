<?php

namespace tests\User;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use DateTime;

class PasswordTest extends \tests\TestCase
{
    public function testUnknownUser() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('PUT', '/api/user/5f92f0e9fde8b71d307d703b/password');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testAdminRemovePassword() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Regular user with password
        static::assertNotEmpty($this->user->password);

        // An admin block the user
        $url = '/api/user/' . $this->user->getId() . '/password';
        $req = \photon\test\HTTP::baseRequest('DELETE', $url);
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);

        // User have expiration date
        $this->user->reload();
        static::assertEquals(null, $this->user->password);
    }

    public function testAdminSetPassword() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Regular user with password
        static::assertNotEmpty($this->user->password);

        // An admin block the user
        $password = '83816eba-13a4-11eb-8588-93ff0f53b970';
        $payload = array(
          'password' => $password
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/password';
        $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);

        // User have expiration date
        $this->user->reload();
        $valid = $this->user->verifyPassword($password);
        static::assertEquals(true, $valid);
        $valid = $this->user->verifyPassword('BAD PASSWORD');
        static::assertEquals(false, $valid);
    }

    public function testUserSetPassword() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

        // Regular user with password
        static::assertNotEmpty($this->user->password);

        // An admin block the user
        $password = '83816eba-13a4-11eb-8588-93ff0f53b970';
        $payload = array(
          'password' => $password
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/password';
        $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);

        // User have expiration date
        $this->user->reload();
        $valid = $this->user->verifyPassword($password);
        static::assertEquals(true, $valid);
        $valid = $this->user->verifyPassword('BAD PASSWORD');
        static::assertEquals(false, $valid);
    }

    public function testUserSetPasswordNotHim() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Regular user with password
        static::assertNotEmpty($this->user->password);

        // Update the password of another user
        $password = '83816eba-13a4-11eb-8588-93ff0f53b970';
        $payload = array(
          'password' => $password
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->admin->getId() . '/password';
        $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);
    }

    public function testUserSetPasswordNoPayload() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

        // Invalid payload
        $payload = array(
          'my' => 'bad'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/password';
        $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(400, $resp->status_code);
    }
}
