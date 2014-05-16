<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter.
 */

namespace Drupal\entity_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to display image captions and align images.
 *
 * @Filter(
 *   id = "entity_embed",
 *   title = @Translation("Display embedded entities."),
 *   description = @Translation("Embeds entities using data attributes: data-entity-type, data-entity-uuid or data-entity-id, and data-view-mode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EntityEmbedFilter extends FilterBase {

  protected $postRender = array();
  protected $cacheTags = array();

  public function clearPostRender() {
    $this->postRender = array();
    $this->cacheTags = array();
  }

  public function buildPlaceholder($entity, $view_mode, $langcode) {
    $callback = '\Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter::postRender';
    $context = array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'view_mode' => $view_mode,
      'langcode' => $langcode,
      'token' => drupal_render_cache_generate_token(),
    );
    $this->postRender[$callback][$context['token']] = $context;
    $this->cacheTags[] = $entity->getCacheTag();
    return drupal_render_cache_generate_placeholder($callback, $context, $context['token']);
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if (stristr($text, 'data-entity-type') !== FALSE && stristr($text, 'data-view-mode') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      $this->clearPostRender();
      foreach ($xpath->query('//*[@data-entity-type and @data-view-mode]') as $node) {
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;
        $view_mode = $node->getAttribute('data-view-mode');

        // Load the entity either by UUID or ID.
        if ($node->hasAttribute('data-entity-uuid')) {
          $uuid = $node->getAttribute('data-entity-uuid');
          $entity = entity_load_by_uuid($entity_type, $uuid);
        }
        if ($node->hasAttribute('data-entity-id')) {
          $id = $node->getAttribute('data-entity-id');
          $entity = entity_load($entity_type, $id);
          // Add the entity UUID.
          if ($entity && $uuid = $entity->uuid()) {
            $node->setAttribute('data-entity-uuid', $uuid);
          }
        }

        if (!empty($entity)) {
          $placeholder = $this->buildPlaceholder($entity, $view_mode, $langcode);

          // Load the altered HTML into a new DOMDocument and retrieve the element.
          $updated_node = Html::load($placeholder)->getElementsByTagName('body')
            ->item(0)
            ->childNodes
            ->item(0);
          // Import the updated node from the new DOMDocument into the original
          // one, importing also the child nodes of the updated node.
          $updated_node = $dom->importNode($updated_node, TRUE);
          // Finally, replace the original entity node with the new entity node!
          $node->appendChild($updated_node);
        }
      }

      $text = Html::serialize($dom);

      if (!empty($this->postRender) || !empty($this->cacheTags)) {
        $return = array(
          '#markup' => $text,
          '#post_render_cache' => $this->postRender,
          '#cache' => array(
            'tags' => NestedArray::mergeDeepArray($this->cacheTags),
          ),
        );
        return $return;
      }
    }

    return $text;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('
        <p>You can embed entities. Additional properties can be added to the embed tag like data-caption and data-align if supported. Examples:</p>
        <ul>
          <li>Embed by ID: <code>&lt;div data-entity-type="node" data-entity-id="1" data-view-mode="teaser" /&gt;</code></li>
          <li>Embed by UUID: <code>&lt;div data-entity-type="node" data-entity-uuid="07bf3a2e-1941-4a44-9b02-2d1d7a41ec0e" data-view-mode="teaser" /&gt;</code></li>
        </ul>');
    }
    else {
      return $this->t('You can embed entities.');
    }
  }

  public static function postRender(array $element, array $context) {
    $callback = '\Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter::postRender';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context, $context['token']);
    // If this text filter is used alongside FilterHtmlCorrector, then we need
    // to be sure to check for both formats of the render cache placeholder:
    // Original placeholder:
    // <drupal:render-cache-placeholder .. />
    // After FilterHtmlCorrector::process():
    // <render-cache-placeholder ... ></render-cache-placeholder>
    $alt_placeholder = Html::normalize($placeholder);

    $entity = entity_load($context['entity_type'], $context['entity_id']);
    if ($entity && $entity->access()) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 10) {
        throw new RecursiveRenderingException(format_string('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $item->entity->getEntityTypeId(), '@entity_id' => $item->target_id)));
      }

      // Build the rendered entity.
      $build = entity_view($entity, $context['view_mode'], $context['langcode']);

      // Hide entity links by default.
      // @todo Make this configurable via data attribute?
      if (isset($build['links'])) {
        $build['links']['#access'] = FALSE;
      }

      $entity_output = drupal_render($build);

      $depth--;
      $element['#markup'] = str_replace($placeholder, $entity_output, $element['#markup']);
      $element['#markup'] = str_replace($alt_placeholder, $entity_output, $element['#markup']);
    }
    else {
      $element['#markup'] = str_replace($placeholder, '', $element['#markup']);
      $element['#markup'] = str_replace($alt_placeholder, '', $element['#markup']);
    }
    return $element;
  }
}
