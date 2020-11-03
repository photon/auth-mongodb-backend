<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBBackend;

class AclsTest extends TestCase
{
    public function testListAclsNotConnected()
    {
        $dispatcher = new \photon\core\Dispatcher;

      // List users on an empty database (not connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/acl');
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(403, $resp->status_code);
    }

    public function testListAclsConnectedAsUser()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createUser();

      // List users on an empty database (not connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/acl');
        $req->user = $this->user;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(403, $resp->status_code);
    }

    public function testListAclsConnectedAsAdmin()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

      // List users on an empty database (not connected)
        $req = \photon\test\HTTP::baseRequest('GET', '/api/acl');
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(200, $resp->status_code);
    }

    public function testCreateAclsWithoutName()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

      // Create the acl 'Jungler'
        $payload = array(
        'TYPO' => 'printer'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/acl';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(400, $resp->status_code);
    }

    public function testCreateAcls()
    {
        $dispatcher = new \photon\core\Dispatcher;

        $this->createAdmin();

      // Create the acl 'Jungler'
        $payload = array(
        'name' => 'printer'
        );
        $stream = fopen('data:text/plain;base64,' . base64_encode(json_encode($payload) . "\n"), 'rb');
        $url = '/api/acl';
        $req = \photon\test\HTTP::baseRequest('POST', $url, null, $stream, array(), array('content-type' => 'application/json'));
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(201, $resp->status_code);
        $json = json_decode($resp->content);
        $this->assertNotEquals(false, $json);

      // Reload acl (generate exception if do not exists)
        $acl = new \photon\auth\MongoDBAcl(array('name' => 'printer'));

      // Get the acl
        $url = '/api/acl/' . $acl->getId();
        $req = \photon\test\HTTP::baseRequest('GET', $url);
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(200, $resp->status_code);

      // Delete the acl
        $url = '/api/acl/' . $acl->getId();
        $req = \photon\test\HTTP::baseRequest('DELETE', $url);
        $req->user = $this->admin;
        list($req, $resp) = $dispatcher->dispatch($req);
        $this->assertEquals(204, $resp->status_code);
    }
}
