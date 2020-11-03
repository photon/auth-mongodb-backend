<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBAcl;
use photon\auth\MongoDBBackend;

class TestCase extends \photon\test\TestCase
{
    protected $acl = null;
    protected $group = null;
    protected $admin = null;
    protected $user = null;
    protected $user2 = null;

    public function setUp() : void
    {
        parent::setUp();

        // Cleanup database
        $db = \photon\db\Connection::get('default');
        $db->drop();

        // Install indexes
        MongoDBBackend::createIndex();

        // Install ACL
        MongoDBAcl::ensureExists(array(
          'admin-users',
        ));

        // Install endpoints
        $urls = Conf::f('urls', array());

        // Install API and append error 500 on unknown API
        $endpoints = MongoDB\APICommon::getURLs();
        $error500 = array(
          'regex' => '#(.*)#',
          'view' => function($request, $match) {
              throw new \photon\views\APIJson\Exception;
          },
        );
        $endpoints[] = $error500;

        $urls[] = array(
          'regex' => '#^/api/#',
          'sub' => $endpoints,
        );

        // Catch all other requets to avoid 404 automatic responses
        // We have real 404 answer to be tested
        $urls[] = $error500;

        Conf::set('urls', $urls);
    }

    protected function createAcl()
    {
      $acl = new \photon\auth\MongoDBAcl;
      $acl->setName('x86-64');
      $acl->save();

      $this->acl = $acl;
    }

    protected function createGroup()
    {
      $group = new \photon\auth\MongoDBGroup;
      $group->setName('Mun');
      $group->save();

      $this->group = $group;
    }

    protected function createUser()
    {
      $user = new \photon\auth\MongoDBUser;
      $user->setName('Mick Robot');
      $user->setLogin('mr@exemple.com');
      $user->setPassword('secret');
      $user->save();

      $this->user = $user;
    }

    protected function createUser2()
    {
      $user = new \photon\auth\MongoDBUser;
      $user->setName('Cretinous Rabbit');
      $user->setLogin('cr@exemple.com');
      $user->setPassword('secret');
      $user->save();

      $this->user2 = $user;
    }

    protected function createAdmin()
    {
      $user = new \photon\auth\MongoDBUser;
      $user->setName('John DOE');
      $user->setLogin('jd@exemple.com');
      $user->setPassword('strong');
      $user->save();

      $acl = new \photon\auth\MongoDBAcl(array('name' => 'admin-users'));
      $acl->addUser($user);
      $acl->save();

      $this->admin = $user;
    }
}
