<?php

namespace tests\Acl;

use \photon\config\Container as Conf;

class GroupTest extends \tests\TestCase
{
    public function testUnknownAcl()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();

      $url = '/api/acl/5f92f0e9fde8b71d307d703b/group/5f92f0e9fde8b71d307d703b';
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      file_put_contents('/tmp/aaa.html', $resp->content);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testUnkownGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createAcl();

      $url = '/api/acl/' . $this->acl->getId() . '/group/5f92f0e9fde8b71d307d703b';
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(404, $resp->status_code);
    }

    public function testDeleteGroup()
    {
      $dispatcher = new \photon\core\Dispatcher;

      $this->createAdmin();
      $this->createGroup();
      $this->createAcl();

      $this->acl->addGroup($this->group);
      $this->acl->save();

      $url = '/api/acl/' . $this->acl->getId() . '/group/' . $this->group->getId();
      $req = \photon\test\HTTP::baseRequest('DELETE', $url);
      $req->user = $this->admin;
      list($req, $resp) = $dispatcher->dispatch($req);
      $this->assertEquals(204, $resp->status_code);
    }
}
