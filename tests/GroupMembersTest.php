<?php

namespace tests;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class GroupMembersTest extends TestCase
{
    public function testAddRemoveUserInGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      // Create a group
      $group = new \photon\auth\MongoDBGroup;
      $group->setName('Wall-E');
      $group->save();

      // Create an user
      $user = new \photon\auth\MongoDBUser;
      $user->setName('Eve');
      $user->setLogin('eve@space.far');
      $user->setPassword('secret');
      $user->save();

      // Add user Eve in group Wall-E
      $payload = array(
        'member' => (string) $user->getId(),
      );
      $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
      $url = '/api/group/' . $group->getId() . '/member';
      $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(201, $resp->status_code);

      // Check for Eve in the group members
      $url = '/api/group/' . $group->getId();
      $req = \photon\test\HTTP::baseRequest('GET', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(200, $resp->status_code);
      $json = json_decode($resp->content);
      $this->assertNotEquals(false, $json);
      $this->assertNotEmpty($json->users);
      $this->assertEquals((string) $user->getId(), $json->users[0]);

      // Kick her !
      $url = '/api/group/' . $group->getId() . '/member/' . $user->getId();
      $req = \photon\test\HTTP::baseRequest('DELETE', $url, null, $stream, array(), array('content-type' => 'application/json'));
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(204, $resp->status_code);

      // Group must be empty again
      $url = '/api/group/' . $group->getId();
      $req = \photon\test\HTTP::baseRequest('GET', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $json = json_decode($resp->content);
      $this->assertNotEquals(false, $json);
      $this->assertEmpty($json->users);
    }
}
