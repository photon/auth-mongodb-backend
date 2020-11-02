<?php

namespace tests\Group;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class MembersTest extends \tests\TestCase
{
    public function testUnknownGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $url = '/api/group/5f92f0e9fde8b71d307d703b/member/5f92f0e9fde8b71d307d703b';
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testUnkownMember()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();

      $url = '/api/group/' . $this->group->getId() . '/member/5f92f0e9fde8b71d307d703b';
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testDeleteMember()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createUser();
      $this->createGroup();

      $this->group->addUser($this->user);
      $this->group->save();

      $url = '/api/group/' . $this->group->getId() . '/member/' . $this->user->getId();
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(204, $resp->status_code);
    }
}
