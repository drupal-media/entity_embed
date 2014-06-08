<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter.
 */

namespace Drupal\entity_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_embed\RecursiveRenderingException;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to display embedded entities based on data attributes.
 *
 * @Filter(
 *   id = "entity_embed",
 *   title = @Translation("Display embedded entities."),
 *   description = @Translation("Embeds entities using data attributes: data-entity-type, data-entity-uuid or data-entity-id, and data-view-mode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EntityEmbedFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface.
   */
  protected $moduleHandler;

  /**
   * Constructs a EntityEmbedFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && (strpos($text, 'data-entity-embed-display') !== FALSE || strpos($text, 'data-view-mode') !== FALSE)) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//*[@data-entity-type and (@data-entity-uuid or @data-entity-id) and (@data-entity-embed-display or @data-view-mode)]') as $node) {
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;

        try {
          // Load the entity either by UUID (preferred) or ID.
          if ($node->hasAttribute('data-entity-uuid')) {
            $uuid = $node->getAttribute('data-entity-uuid');

            $entity_type_definition = $this->entityManager->getDefinition($entity_type);
            $uuid_key = $entity_type_definition->getKey('uuid');
            $controller = $this->entityManager->getStorage($entity_type);
            $entities = $controller->loadByProperties(array($uuid_key => $uuid));
            $entity = reset($entities);
          }
          elseif ($node->hasAttribute('data-entity-id')) {
            $id = $node->getAttribute('data-entity-id');

            $controller = $this->entityManager->getStorage($entity_type);
            $entity = $controller->load($id);

            // Add the entity UUID attribute to the parent node.
            if ($entity && $uuid = $entity->uuid()) {
              $node->setAttribute('data-entity-uuid', $uuid);
            }
          }

          if (!empty($entity)) {
            $context = array();

            // Set the initial langcode but it can be overridden by a data
            // attribute.
            if (!empty($langcode)) {
              $context['langcode'] = $langcode;
            }

            // Convert the data attributes to the context array.
            foreach ($node->attributes as $attribute) {
              $key = strtr($attribute->nodeName, array('data-' => ''));
              $context[$key] = $attribute->nodeValue;

              // Check for JSON-encoded attributes.
              $data = json_decode($context[$key], TRUE, 10);
              if ($data !== NULL && json_last_error() === JSON_ERROR_NONE) {
                $context[$key] = $data;
              }
            }

            // Support the deprecated view-mode data attribute.
            if (isset($context['view-mode']) && !isset($context['entity-embed-display']) && !isset($context['entity-embed-settings'])) {
              $context['entity-embed-settings']['view_mode'] = $context['view-mode'];
              unset($context['view-mode']);
            }

            $placeholder = $this->buildPlaceholder($entity, $result, $context);
            $this->setDomNodeContent($node, $placeholder);
          }
        }
        catch(\Exception $e) {
          watchdog_exception('entity_embed', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
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

  /**
   * Build a render cache placeholder that will eventually render an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param FilterProcessResult $result
   *   The filter process result object that will have post_render_cache and
   *   cache tags added.
   * @param array $context
   *   (optional) An array of contextual information to be included in the
   *   generated placeholder.
   *
   * @return string
   *   The generated render cache placeholder from
   *   drupal_render_cache_generate_placeholder().
   */
  public function buildPlaceholder(EntityInterface $entity, FilterProcessResult $result, array $context = array()) {
    $callback = get_called_class() . '::postRender';
    $context += array(
      'entity-type' => $entity->getEntityTypeId(),
      'entity-id' => $entity->id(),
      'entity-embed-display' => 'default',
      'entity-embed-settings' => array(),
      'langcode' => NULL,
    );

    // Allow modules to alter the context.
    $this->moduleHandler->alter('entity_embed_context', $context, $callback, $entity);

    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);

    $result->addPostRenderCacheCallback($callback, $context);

    // Add cache tags.
    if ($tags = $entity->getCacheTag()) {
      $result->addCacheTags($tags);
    }

    return $placeholder;
  }

  /**
   * #post_render_cache callback; renders an embedded entity.
   *
   * Replaces the #post_render_cache placeholder with an embedded entity.
   *
   * @param array $element
   *   The renderable array that contains the to be replaced placeholder.
   * @param array $context
   *   An array with the following keys:
   *   - entity-type: The entity type.
   *   - entity-id: The entity ID.
   *   - token: The placeholder token generated in buildPlaceholder().
   *
   * @return array
   *   A renderable array representing the placeholder replaced with the
   *   rendered entity.
   */
  public static function postRender(array $element, array $context) {
    $callback = get_called_class() . '::postRender';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);

    // Do not bother rendering the entity if the placeholder cannot be found.
    if (strpos($element['#markup'], $placeholder) === FALSE) {
      return $element;
    }

    $entity_output = '';
    try {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        throw new RecursiveRenderingException(format_string('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $this->entity->getEntityTypeId(), '@entity_id' => $this->entity->id())));
      }

      if ($entity = entity_load($context['entity-type'], $context['entity-id'])) {
        // @todo, This direct usage can be replaced with injection following
        // https://drupal.org/node/2247779 .
        // @see https://drupal.org/node/2281487 .
        $manager = \Drupal::service('plugin.manager.entity_embed.display');
        $display = $manager->createInstance($context['entity-embed-display'], $context['entity-embed-settings']);
        $display->setContextValue('entity', $entity);
        $display->setAttributes($context);
        if ($display->access()) {
          $build = $display->build();
          // Allow modules to alter the rendered embedded entity.
          \Drupal::moduleHandler()->alter('entity_embed', $build, $display);
          $entity_output = drupal_render($build);
        }
      }

      $depth--;
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }

    $element['#markup'] = str_replace($placeholder, $entity_output, $element['#markup']);
    return $element;
  }

  /**
   * Replace the contents of a DOMNode.
   *
   * @param \DOMNode $node
   *   A DOMNode or DOMElement object.
   * @param string $content
   *   The text or HTML that will replace the contents of $node.
   */
  protected function setDomNodeContent(\DOMNode $node, $content) {
    // Load the contents into a new DOMDocument and retrieve the element.
    $replacement_node = Html::load($content)->getElementsByTagName('body')
      ->item(0)
      ->childNodes
      ->item(0);

    // Import the updated DOMNode from the new DOMDocument into the original
    // one, importing also the child nodes of the replacment DOMNode.
    $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);

    // Remove all children of the DOMNode.
    while ($node->hasChildNodes()) {
      $node->removeChild($node->firstChild);
    }

    // Finally, append the contents to the DOMNode.
    $node->appendChild($replacement_node);
  }
}
