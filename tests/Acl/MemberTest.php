<?php

namespace tests\Acl;

use \photon\config\Container as Conf;

class MemberTest extends \tests\TestCase
{
    public function testUnknownAcl()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $url = '/api/acl/5f92f0e9fde8b71d307d703b/member/5f92f0e9fde8b71d307d703b';
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testUnkownMember()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createAcl();

      $url = '/api/acl/' . $this->acl->getId() . '/member/5f92f0e9fde8b71d307d703b';
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
      $this->createAcl();

      $this->acl->addUser($this->user);
      $this->acl->save();

      $url = '/api/acl/' . $this->acl->getId() . '/member/' . $this->user->getId();
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(204, $resp->status_code);
    }
}
