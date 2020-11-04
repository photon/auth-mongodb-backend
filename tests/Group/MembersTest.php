<?php

namespace tests\Group;

use \photon\config\Container as Conf;

class MembersTest extends \tests\TestCase
{
    public function testUnknownGroup() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('POST', '/api/group/5f92f0e9fde8b71d307d703b/member');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testAddMemberNoPayload() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createGroup();

        $req = \photon\test\HTTP::baseRequest('POST', '/api/group/' . $this->group->getId() . '/member');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(400, $resp->status_code);
    }

    public function testAddMemberBadPayload() : void
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
        static::assertEquals(400, $resp->status_code);
    }

    public function testAddUnkownMember() : void
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
        static::assertEquals(400, $resp->status_code);
    }

    public function testAddMember() : void
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
        static::assertEquals(201, $resp->status_code);

        $this->group->reload();
        static::assertEquals(1, count($this->group->getUsers()));
    }
}
