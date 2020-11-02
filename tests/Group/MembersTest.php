<?php

namespace tests\Group;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class MemberTest extends \tests\TestCase
{
    public function testUnknownGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $req = \photon\test\HTTP::baseRequest('POST', '/api/group/5f92f0e9fde8b71d307d703b/member');
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testAddMemberNoPayload()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();

      $req = \photon\test\HTTP::baseRequest('POST', '/api/group/' . $this->group->getId() . '/member');
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(400, $resp->status_code);
    }

    public function testAddMemberBadPayload()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();

      $payload = array(
        'my' => 'bad'
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/group/' . $this->group->getId() . '/member';
      $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(400, $resp->status_code);
    }

    public function testAddUnkownMember()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();

      $payload = array(
        'member' => '5f92f0e9fde8b71d307d0000',
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/group/' . $this->group->getId() . '/member';
      $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(400, $resp->status_code);
    }

    public function testAddMember()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createUser();
      $this->createGroup();

      $payload = array(
        'member' => (string) $this->user->getId(),
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/group/' . $this->group->getId() . '/member';
      $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(201, $resp->status_code);

      $this->group->reload();
      $this->assertEquals(1, count($this->group->getUsers()));
    }
}
