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
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\Plugin\FilterBase;
use Drupal\entity_reference\RecursiveRenderingException;
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
    if (strpos($text, 'data-entity-type') !== FALSE && strpos($text, 'data-view-mode') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      $build = array(
        '#markup' => $text,
        '#post_render_cache' => array(),
      );

      foreach ($xpath->query('//*[@data-entity-type and @data-view-mode]') as $node) {
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;
        $view_mode = $node->getAttribute('data-view-mode');

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
            $context = array('langcode' => $langcode);
            foreach ($node->attributes as $attribute) {
              $key = strtr($attribute->nodeName, array('data-' => ''));
              $context[$key] = $attribute->nodeValue;
            }

            $placeholder = $this->buildPlaceholder($entity, $build, $context);
            $this->setDomNodeContent($node, $placeholder);
          }
        }
        catch(\Exception $e) {
          watchdog_exception('entity_embed', $e);
        }
      }

      $build['#markup'] = Html::serialize($dom);
      return $build;
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

  /**
   * Build a render cache placeholder that will eventually render an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param array $build
   *   The render array from the process() method, can be altered by reference.
   * @param array $context
   *   (optional) An array of contextual information to be included in the
   *   generated placeholder.
   *
   * @return string
   *   The generated render cache placeholder from
   *   drupal_render_cache_generate_placeholder().
   */
  public function buildPlaceholder(EntityInterface $entity, array &$build, array $context = array()) {
    $callback = get_called_class() . '::postRender';
    $context += array(
      'entity-type' => $entity->getEntityTypeId(),
      'entity-id' => $entity->id(),
      'view-mode' => 'default',
      'langcode' => Language::LANGCODE_DEFAULT,
    );
    // Some context properties should not be set ahead of time.
    $context['render-callback'] = get_called_class() . '::renderEntity';
    $context['token'] = drupal_render_cache_generate_token();

    // Allow modules to alter the context.
    $this->moduleHandler->alter('entity_embed_context', $context, $entity);

    $build['#post_render_cache'][$callback][$context['token']] = $context;

    // Add cache tags.
    if ($tags = $entity->getCacheTag()) {
      if (!isset($build['#cache']['tags'])) {
        $build['#cache']['tags'] = array();
      }
      $build['#cache']['tags'] = NestedArray::mergeDeepArray($build['#cache']['tags'], $tags);
    }

    return drupal_render_cache_generate_placeholder($callback, $context, $context['token']);
  }

  public static function postRender(array $element, array $context) {
    $callback = get_called_class() . '::postRender';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context, $context['token']);

    if (strpos($element['#markup'], $placeholder) === FALSE) {
      // If the text filter is used alongside FilterHtmlCorrector, then we need
      // to check for an alternate version of the render cache placeholder:
      // Original placeholder:
      // <drupal:render-cache-placeholder .. />
      // After FilterHtmlCorrector::process():
      // <render-cache-placeholder ... ></render-cache-placeholder>
      // @todo Remove this when fixed in Drupal core.
      $placeholder = Html::normalize($placeholder);
    }

    // Do not bother rendering the entity if the placeholder cannot be found.
    if (strpos($element['#markup'], $placeholder) === FALSE) {
      return $element;
    }

    $entity_output = '';
    try {
      $render_callback = $context['render-callback'];
      $entity = entity_load($context['entity-type'], $context['entity-id']);
      if ($entity && $entity->access('view')) {
        $entity_output = call_user_func_array($render_callback, array($entity, $context));
      }
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

  /**
   * Renders an entity using the post_render_cache context.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being rendered.
   * @param array $context
   *   A post_render_cache context array. The required key/value pairs are
   *
   * @return string
   *   The rendered entity HTML, or an empty string on failure.
   *
   * @see \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter::buildPlaceholder()
   */
  public static function renderEntity(EntityInterface $entity, array $context) {
    try {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        throw new RecursiveRenderingException(format_string('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $item->entity->getEntityTypeId(), '@entity_id' => $item->target_id)));
      }

      // Build the rendered entity.
      $entity->entity_embed_context = $context;
      $build = entity_view($entity, $context['view-mode'], $context['langcode']);

      // Hide entity links by default.
      // @todo Make this configurable via data attribute?
      if (isset($build['links'])) {
        $build['links']['#access'] = FALSE;
      }

      // Allow modules to alter the rendered embedded entity.
      \Drupal::moduleHandler()->alter('entity_embed', $build, $entity, $context);

      $entity_output = drupal_render($build);

      $depth--;

      return $entity_output;
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }

    // In case of failures return an empty string.
    return '';
  }
}
