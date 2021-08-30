<?php

namespace Drupal\social_flex;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

// @note: You only need Reference, if you want to change service arguments.
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies social flexible group services.
 */
class SocialFlexServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides services.
    
    // Note: it's safest to use hasDefinition() first, because getDefinition() will 
    // throw an exception if the given service doesn't exist.
    // ContentAccessCheck
    if ($container->hasDefinition('social_group_flexible_group_access.group.permission')) {
      $definition = $container->getDefinition('social_group_flexible_group_access.group.permission');
      $definition->setClass('Drupal\social_flex\Access\SocialFlexContentAccessCheck')
        ->addArgument(new Reference('social_flex.common'));
    }

    // JoinPermissionCheck
    if ($container->hasDefinition('social_group_flexible_group_access.flexible_group.permission')) {
      $definition = $container->getDefinition('social_group_flexible_group_access.flexible_group.permission');
      $definition->setClass('Drupal\social_flex\Access\SocialFlexJoinPermissionAccessCheck')
        ->addArgument(new Reference('social_flex.common'));
    }

    // RouteSubscriber
    if ($container->hasDefinition('social_group_flexible_group_access.route_subscriber')) {
      $definition = $container->getDefinition('social_group_flexible_group_access.route_subscriber');
      $definition->setClass('Drupal\social_flex\Subscriber\Route')
        ->addArgument(new Reference('social_flex.common'));
    }

    // EventSubscriber
    if ($container->hasDefinition('social_group_flexible_group.redirect_subscriber')) {
      $definition = $container->getDefinition('social_group_flexible_group.redirect_subscriber');
      $definition->setClass('Drupal\social_flex\EventSubscriber\RedirectSubscriber')
        ->addArgument(new Reference('social_flex.common'));
    }

    // ConfigOverride    
    if ($container->hasDefinition('social_group_flexible_group.config_override')) {
      $definition = $container->getDefinition('social_group_flexible_group.config_override');
      $definition->setClass('Drupal\social_flex\SocialFlexConfigOverride')
        ->addArgument(new Reference('social_flex.common'));
    } 
       

  }
}
