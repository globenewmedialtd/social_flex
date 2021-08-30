<?php

namespace Drupal\social_flex\Access;

use Drupal\social_group_flexible_group\Access\FlexibleGroupContentAccessCheck;
use Drupal\group\Entity\Group;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\GroupMembership;
use Symfony\Component\Routing\Route;
use Drupal\social_flex\SocialFlexCommonService;


/**
 * Determines access to routes based flexible_group membership and settings.
 */
class SocialFlexContentAccessCheck extends FlexibleGroupContentAccessCheck {

  /**
   * The social flex common service.
   *
   * @var \Drupal\social_flex\SocialFlexCommonService
   */
  protected $socialFlexCommonService;

  /**
   * SocialFlexContentAccessCheck constructor.
   *
   * @param \Drupal\social_flex\SocialFlexCommonService $social_flex_common_service
   *   The social flex common serivce.
   */
  public function __construct(
    SocialFlexCommonService $social_flex_common_service
  ) {
    $this->socialFlexCommonService = $social_flex_common_service;
  }

  /**
   * Checks access.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    
    parent::access($route, $route_match, $account);
    
    $permission = $route->getRequirement('_flexible_group_content_visibility');

    // Don't interfere if no permission was specified.
    if ($permission === NULL) {
      return AccessResult::allowed();
    }

    // Don't interfere if no group was specified.
    $parameters = $route_match->getParameters();
    if (!$parameters->has('group')) {
      return AccessResult::allowed();
    }

    // Don't interfere if the group isn't a real group.
    $group = $parameters->get('group');
    if (!$group instanceof Group) {
      return AccessResult::allowed();
    }

    // A user with this access can definitely do everything.
    if ($account->hasPermission('manage all groups')) {
      return AccessResult::allowed();
    }

    // Handling the visibility of a group.
    if ($group->hasField('field_flexible_group_visibility')) {
      $group_visibility_value = $group->getFieldValue('field_flexible_group_visibility', 'value');
      $is_member = $group->getMember($account) instanceof GroupMembership;
      $uid = $account->id();
      $user = \Drupal::service('entity_type.manager')->getStorage('user')->load($uid); 
      
      switch ($group_visibility_value) {
        case 'members':
          if (!$is_member) {
            return AccessResult::forbidden();
          }
          break;

        case 'community':
          if ($account->isAnonymous()) {
            return AccessResult::forbidden();
          }
          break;
        case 'community_role':
          if (!$user->hasRole('internal')) {
            return AccessResult::forbidden();
          }
          break;
      }
    }

    // Don't interfere if the group isn't a flexible group.
    $isFlexibleGroup = $this->socialFlexCommonService->isFlexibleGroup($group);
    if (!$isFlexibleGroup) {
      return AccessResult::allowed();
    }

    // AN Users aren't allowed anything if Public isn't an option.
    if (!$account->isAuthenticated() && !social_group_flexible_group_public_enabled($group)) {
      return AccessResult::forbidden();
    }

    // If User is a member we can also rely on Group to take permissions.
    if ($group->getMember($account) !== FALSE) {
      return AccessResult::allowed()->addCacheableDependency($group);
    }

    // It's a non member but Community isn't enabled.
    // No access for you only for the about page.
    if ($account->isAuthenticated() && !social_group_flexible_group_community_enabled($group)
      && !social_group_flexible_group_public_enabled($group) && !social_community_role_community_role_enabled($group) 
      && $route_match->getRouteName() !== 'view.group_information.page_group_about'
      && $route_match->getRouteName() !== 'entity.group.canonical'
      && $route_match->getRouteName() !== 'view.group_members.page_group_members') {
      return AccessResult::forbidden()->addCacheableDependency($group);
    }

    // We allow it but lets add the group as dependency to the cache
    // now because field value might be edited and cache should
    // clear accordingly.
    return AccessResult::allowed()->addCacheableDependency($group);
  }



}
