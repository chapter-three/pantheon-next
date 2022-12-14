<?php

namespace Drupal\next_for_drupal_pantheon\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for PantheonNext entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 *
 * @ingroup next_for_drupal_pantheon
 */
class PantheonNextHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);
    if ($environment = $this->getEnvironmentVariablesRoute($entity_type)) {
      $collection->add("entity.next_for_drupal_pantheon.environment", $environment);
    }
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass() && ($admin_permission = $entity_type->getAdminPermission())) {
      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route->addDefaults([
        '_entity_list' => $entity_type->id(),
        '_title' => 'Pantheon Next.js Sites',
      ])->setRequirement('_permission', $admin_permission);
      return $route;
    }
  }

  /**
   * Gets the environment_variables page route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEnvironmentVariablesRoute(EntityTypeInterface $entity_type) {
    if (!$entity_type->hasLinkTemplate('environment')) {
      return NULL;
    }

    $route = new Route($entity_type->getLinkTemplate('environment'));
    $route->setDefault('_entity_form', 'next_for_drupal_pantheon.environment');
    $route->setDefault('_title', 'Environment Variables');
    $route->setRequirement('_permission', $entity_type->getAdminPermission());
    $route->setOption('_admin_route', TRUE);
    $route->setOption('parameters', [
      'next_for_drupal_pantheon' => [
        'type' => 'entity:next_for_drupal_pantheon',
      ],
    ]);

    return $route;
  }

}
