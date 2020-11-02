<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBAcl;
use photon\auth\MongoDBBackend;

class TestCase extends \photon\test\TestCase
{
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

        // Install API endpoints
        $urls = Conf::f('urls', array());
        $urls[] = array(
          'regex' => '#^/api#',
          'sub' => MongoDB\APICommon::getURLs(),
        );
        Conf::set('urls', $urls);

        // Install ACL
        MongoDBAcl::ensureExists(array(
          'admin-users',
        ));
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
