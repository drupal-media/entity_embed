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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * Expects the filter format and the HTML element as GET parameters.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The preview of the entity specified by the data attributes.
   */
  public function preview() {
    $text = $this->request->get('value');
    $filter_format = $this->request->get('filter-format');
    if ($text == '' || $filter_format == '') {
      throw new NotFoundHttpException();
    }

    $entity_output = check_markup($text, $filter_format);
    return new JsonResponse(array(
      'content' => $entity_output,
    ));
  }

}
