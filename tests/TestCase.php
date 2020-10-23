<?php

namespace tests;

use photon\config\Container as Conf;
use photon\auth\api\MongoDB;
use photon\auth\MongoDBAcl;
use photon\auth\MongoDBBackend;

class TestCase extends \photon\test\TestCase
{
    protected $admin = null;
    protected $user = null;

    public function setup()
    {
        parent::setup();

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

    protected function createUser()
    {
      $user = new \photon\auth\MongoDBUser;
      $user->setName('Mick Robot');
      $user->setLogin('mr@exemple.com');
      $user->setPassword('secret');
      $user->save();

      $this->user = $user;
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
