<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedController
 */

namespace Drupal\entity_embed;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for EntityEmbed module routes.
 */
class EntityEmbedController extends ControllerBase {
  use EntityHelperTrait;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a EntityEmbedController object.
   *
   * @param Symfony\Component\HttpFoundation\Request $plugin_manager
   *   The Module Handler.
   */
  public function __construct(Request $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request')
    );
  }

  /**
   * Returns an Ajax response to generate preview of an entity.
   *
   * Expects the data attributes to be provided as GET parameters.
   */
  public function preview() {
    $context = $this->request->query->all();
    $entity_embed_settings = isset($context['data-entity-embed-settings']) ? Json::decode($context['data-entity-embed-settings']) : array();
    $entity_output = 'No preview available.';
    try {
      $entity = Null;
      if ($context['data-entity-uuid']) {
        $entity = $this->loadEntity($context['data-entity-type'], $context['data-entity-uuid']);
      }
      elseif ($context['data-entity-id']) {
        $entity = $this->loadEntity($context['data-entity-type'], $context['data-entity-id']);
      }

      $display_plugin = isset($context['data-entity-embed-display']) ? $context['data-entity-embed-display'] : 'default';

      if ($entity) {
        $entity_output = $this->renderEntityEmbedDisplayPlugin(
          $entity,
          $display_plugin,
          $entity_embed_settings,
          $context
        );
      }
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }
    return new JsonResponse(array(
      'content' => $entity_output,
    ));
  }

}
