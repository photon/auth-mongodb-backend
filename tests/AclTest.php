<?php

namespace tests;

use photon\config\Container as Conf;

class AclTest extends TestCase
{
    public function testUnknownAcl() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/acl/5f92f0e9fde8b71d307d703b');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        file_put_contents('/tmp/a.html', $resp->content);
        static::assertEquals(404, $resp->status_code);
    }

    public function testReadAcl() : void
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

        $acl = new \photon\auth\MongoDBAcl;
        $acl->setName('Testing');
        $acl->save();

        $req = \photon\test\HTTP::baseRequest('GET', '/api/acl/' . $acl->getId());
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        static::assertEquals(200, $resp->status_code);
        $json = json_decode($resp->content);
        static::assertNotEquals(false, $json);
    }
}
