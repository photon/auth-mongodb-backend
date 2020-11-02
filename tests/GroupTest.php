<?php

namespace tests;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class GroupTest extends TestCase
{
    public function testReadGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();

      $req = \photon\test\HTTP::baseRequest('GET', '/api/group/' . $this->group->getId());
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(200, $resp->status_code);
      $json = json_decode($resp->content);
      $this->assertNotEquals(false, $json);
    }
}
