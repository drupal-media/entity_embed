<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter.
 */

namespace Drupal\entity_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to display embedded entities based on data attributes.
 *
 * @Filter(
 *   id = "entity_embed",
 *   title = @Translation("Display embedded entities."),
 *   description = @Translation("Embeds entities using data attributes: data-entity-type, data-entity-uuid or data-entity-id, and data-view-mode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   cache = FALSE
 * )
 */
class EntityEmbedFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode, $cache, $cache_id) {
    if (stristr($text, 'data-entity-type') !== FALSE && stristr($text, 'data-view-mode') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//*[@data-entity-type and @data-view-mode]') as $node) {
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;
        $view_mode = $node->getAttribute('data-view-mode');

        // Load the entity either by UUID (preferred) or ID.
        if ($node->hasAttribute('data-entity-uuid')) {
          $uuid = $node->getAttribute('data-entity-uuid');
          $entity = entity_load_by_uuid($entity_type, $uuid);
        }
        elseif ($node->hasAttribute('data-entity-id')) {
          $id = $node->getAttribute('data-entity-id');
          $entity = entity_load($entity_type, $id);
          // Add the entity UUID attribute to the parent node.
          if ($entity && $uuid = $entity->uuid()) {
            $node->setAttribute('data-entity-uuid', $uuid);
          }
        }

        // Check if entity exists and we have entity access.
        if ($entity && $entity->access()) {

          // Protect ourselves from recursive rendering.
          static $depth = 0;
          $depth++;
          if ($depth > 20) {
            throw new RecursiveRenderingException(format_string('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $item->entity->getEntityTypeId(), '@entity_id' => $item->target_id)));
          }

          // Build the rendered entity.
          $build = entity_view($entity, $view_mode, $langcode);

          // Hide entity links by default.
          // @todo Make this configurable via data attribute?
          if (isset($build['links'])) {
            $build['links']['#access'] = FALSE;
          }
          $output = drupal_render($build);

          // Load the altered HTML into a new DOMDocument and retrieve the
          // element.
          $updated_node = Html::load($output)->getElementsByTagName('body')
            ->item(0)
            ->childNodes
            ->item(0);
          // Import the updated node from the new DOMDocument into the original
          // one, importing also the child nodes of the updated node.
          $updated_node = $dom->importNode($updated_node, TRUE);

          // Remove all children of the node from the existing DOMDocument.
          while ($node->hasChildNodes() == TRUE) {
            $node->removeChild($node->firstChild);
          }
          // Finally, append the entity to the DOM node.
          $node->appendChild($updated_node);

          return Html::serialize($dom);
        }
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
}
