<?php

namespace photon\auth\api\MongoDB;

use photon\storage\mongodb\Obj;
use photon\auth\MongoDBBackend;
use photon\views\APIJson;

class APICommon extends APIJson\Rest
{
    protected $handleCORS = true;

    protected $isAdmin = false;
    protected $config = null;

    public function __construct()
    {
      $this->config = MongoDBBackend::getConfig();
    }

    protected function getAclClass()
    {
      return $this->config['acl_class'];
    }

    protected function getTokenClass()
    {
      return $this->config['token_class'];
    }

    protected function getUserClass()
    {
      return $this->config['user_class'];
    }

    protected function getGroupClass()
    {
      return $this->config['group_class'];
    }

    protected function getUserQueryById($id)
    {
      return [
        $this->config['user_id'] => $this->config['user_class']::createObjectID($id),
      ];
    }

    protected function getGroupQueryById($id)
    {
      return [
        '_id' => $this->config['group_class']::createObjectID($id),
      ];
    }

    protected function getTokenQueryById($id)
    {
      return [
        '_id' => $this->config['token_class']::createObjectID($id),
      ];
    }

    protected function getAclQueryById($id)
    {
      return [
        '_id' => $this->config['acl_class']::createObjectID($id),
      ];
    }

    protected function getPreconditionClass()
    {
      return $this->config['precondition_class'];
    }

    protected function getPreconditionAdmin()
    {
      return $this->config['admin_precondition'];
    }

    protected function ensureUserIsConnected($request)
    {
      $connected = $this->config['user_class']::connected($request);
      if ($connected === true) {
        return;
      }

      throw new APIJson\Exception\Forbidden;
    }

    protected function userIsAdmin($request)
    {
      $class = $this->getPreconditionClass();
      $name = $this->getPreconditionAdmin();

      return $class::$name($request) === true;
    }

    protected function userIsMe($request, $user)
    {
      $targetUser = (string) $user->getId();
      $connectedUser = (string) $request->user->getId();

      return $targetUser === $connectedUser;
    }

    protected function ensureUserIsAdmin($request)
    {
      $admin = $this->userIsAdmin($request);
      if ($admin === true) {
        return;
      }

      throw new APIJson\Exception\Forbidden;
    }

    static public function getURLs()
    {
      return array(
        /* User */
        array('regex' => '#user/(\w+)/password#',
              'view' => array(User\Password::class, 'router'),
              'name' => 'api_user_password_view'),

        array('regex' => '#user/(\w+)/block$#',
              'view' => array(User\Block::class, 'router'),
              'name' => 'api_user_block_view'),

        array('regex' => '#user/(\w+)/expire#',
              'view' => array(User\Expire::class, 'router'),
              'name' => 'api_user_expire_view'),

        array('regex' => '#user/(\w+)/token/(\w+)#',
              'view' => array(User\Token::class, 'router'),
              'name' => 'api_user_tokens_view'),

        array('regex' => '#user/(\w+)/token#',
              'view' => array(User\Tokens::class, 'router'),
              'name' => 'api_user_tokens_view'),

        array('regex' => '#user/(\w+)#',
              'view' => array(User::class, 'router'),
              'name' => 'api_user_view'),

        array('regex' => '#user$#',
              'view' => array(Users::class, 'router'),
              'name' => 'api_users_view'),

        /* Group */
        array('regex' => '#group/(\w+)/member/(\w+)$#',
              'view' => array(Group\Member::class, 'router'),
              'name' => 'api_group_member_view'),

        array('regex' => '#group/(\w+)/member$#',
              'view' => array(Group\Members::class, 'router'),
              'name' => 'api_group_members_view'),

        array('regex' => '#group/(\w+)$#',
              'view' => array(Group::class, 'router'),
              'name' => 'api_group_view'),

        array('regex' => '#group$#',
              'view' => array(Groups::class, 'router'),
              'name' => 'api_groups_view'),

        /* ACL */
        array('regex' => '#acl/(\w+)/group/(\w+)$#',
              'view' => array(Acl\Group::class, 'router'),
              'name' => 'api_acl_group_view'),

        array('regex' => '#acl/(\w+)/group$#',
              'view' => array(Acl\Groups::class, 'router'),
              'name' => 'api_acl_groups_view'),

        array('regex' => '#acl/(\w+)/member/(\w+)$#',
              'view' => array(Acl\Member::class, 'router'),
              'name' => 'api_acl_member_view'),

        array('regex' => '#acl/(\w+)/member$#',
              'view' => array(Acl\Members::class, 'router'),
              'name' => 'api_acl_members_view'),

        array('regex' => '#acl/(\w+)$#',
              'view' => array(Acl::class, 'router'),
              'name' => 'api_acl_view'),

        array('regex' => '#acl$#',
              'view' => array(Acls::class, 'router'),
              'name' => 'api_acls_view'),
      );
    }
}
