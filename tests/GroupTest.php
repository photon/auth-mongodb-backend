<?php

namespace tests;

use \photon\config\Container as Conf;
use \photon\auth\api\MongoDB;

class GroupTest extends TestCase
{

    public function testUnknownGroup() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/group/5f92f0e9fde8b71d307d703b');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(404, $resp->status_code);
    }

    public function testReadGroup() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();
        $this->createGroup();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/group/' . $this->group->getId());
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
    }
}
