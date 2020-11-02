<?php

namespace tests\User;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use DateTime;

class ExpireTest extends \tests\TestCase
{
    public function testUnknownUser()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $req = \photon\test\HTTP::baseRequest('PUT', '/api/user/5f92f0e9fde8b71d307d703b/expire');
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testSetExpire()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Freshly created user do not have expiration date
        $expire = $this->user->getExpirationDate();
        $this->assertEquals(null, $expire);

        // An admin block the user
        $date = '2020-10-21T13:37:42+00:00';
        $payload = array(
          'expire' => $date
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/user/' . $this->user->getId() . '/expire';
        $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(204, $resp->status_code);

        // User have expiration date
        $this->user->reload();
        $expire = $this->user->getExpirationDate(true);
        $this->assertEquals($date, $expire);
    }

    public function testClearExpire()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createUser();

        // Block the user
        $date = '2020-10-21T13:37:42+00:00';
        $this->user->setExpirationDate(new DateTime($date));
        $this->user->save();
        $this->user->reload();
        $expire = $this->user->getExpirationDate();
        $this->assertNotEquals(null, $expire);

        // An admin unblock the user
        $req = \photon\test\HTTP::baseRequest('DELETE', '/api/user/' . $this->user->getId() . '/expire');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(204, $resp->status_code);

        // User is unblocked
        $this->user->reload();
        $expire = $this->user->getExpirationDate(true);
        $this->assertEquals(null, $expire);
    }

    public function testUserExpireBadPayload()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createUser();

      // No expire field
      $payload = array(
        'bad' => 'name'
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/user/' . $this->user->getId() . '/expire';
      $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(400, $resp->status_code);

      // Bad expire field
      $payload = array(
        'expire' => 'Le vendredi 42 septembre 1987 à 18h45'
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/user/' . $this->user->getId() . '/expire';
      $req = \photon\test\HTTP::baseRequest('PUT', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(400, $resp->status_code);
    }
}
