<?php

namespace tests;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class GroupsTest extends TestCase
{
    public function testListGroups() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        // List groups on an empty database (not connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/group');
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(403, $resp->status_code);

        $this->createAdmin();

        // List groups on an empty database (admin connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/group');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(0, count($json));

        // Create a group
        $group = new \photon\auth\MongoDBGroup;
        $group->setName('Top');
        $group->save();

        // List groups (admin connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/group');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
        static::assertEquals(1, count($json));
    }

    public function testCreateDeleteGroup() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

      // Create the group 'Jungler'
        $payload = array(
        'name' => 'Jungler'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/group';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(201, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);

      // Reload group (generate exception if do not exists)
        $group = new \photon\auth\MongoDBGroup(array('name' => 'Jungler'));

      // Delete the group
        $url = '/api/group/' . $group->getId();
        $req = \photon\test\HTTP::baseRequest('DELETE', $url);
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(204, $resp->status_code);
    }

    public function testCreateGroupBadPayload() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $payload = array(
        'bad' => 'name'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/group';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(400, $resp->status_code);
    }
}
