<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter.
 */

namespace Drupal\entity_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\entity_embed\EntityHelperTrait;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to display embedded entities based on data attributes.
 *
 * @Filter(
 *   id = "entity_embed",
 *   title = @Translation("Display embedded entities"),
 *   description = @Translation("Embeds entities using data attributes: data-entity-type, data-entity-uuid or data-entity-id, and data-view-mode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EntityEmbedFilter extends FilterBase implements ContainerFactoryPluginInterface {
  use EntityHelperTrait;

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
    $this->setEntityManager($entity_manager);
    $this->setModuleHandler($module_handler);
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
        $entity_output = '';

        try {
          // Load the entity either by UUID (preferred) or ID.
          $id = $node->getAttribute('data-entity-uuid') ?: $node->getAttribute('data-entity-id');
          $entity = $this->loadEntity($entity_type, $id);

          if ($entity) {
            // If a UUID was not used, but is available, add it to the HTML.
            if (!$node->getAttribute('data-entity-uuid') && $uuid = $entity->uuid()) {
              $node->setAttribute('data-entity-uuid', $uuid);
            }
          }

          if ($entity) {
            $context = array();

            // Convert the data attributes to the context array.
            foreach ($node->attributes as $attribute) {
              $key = $attribute->nodeName;
              $context[$key] = $attribute->nodeValue;

              // Check for JSON-encoded attributes.
              $data = json_decode($context[$key], TRUE, 10);
              if ($data !== NULL && json_last_error() === JSON_ERROR_NONE) {
                $context[$key] = $data;
              }
            }

            // Support the deprecated view-mode data attribute.
            if (isset($context['data-view-mode']) && !isset($context['data-entity-embed-display']) && !isset($context['data-entity-embed-settings'])) {
              $context['data-entity-embed-display'] = 'default';
              $context['data-entity-embed-settings'] = ['view_mode' => &$context['data-view-mode']];
            }

            // Merge in default attributes.
            $context += array(
              'data-entity-id' => $entity->id(),
              'data-entity-embed-display' => 'default',
              'data-entity-embed-settings' => array(),
              'data-langcode' => $langcode,
            );

            // Allow modules to alter the context.
            $this->moduleHandler()->alter('entity_embed_context', $context, $callback, $entity);

            $access = $entity->access('view', NULL, TRUE);
            $accessMetaData = CacheableMetadata::createFromObject($access);
            $entityMetadata = CacheableMetadata::createFromObject($entity);

            // @todo Swap these https://www.drupal.org/node/2516802 is fixed in core.
            $result->addCacheContexts($accessMetaData->getCacheContexts());
            $result->addCacheTags($accessMetaData->getCacheTags());
            $result->addCacheContexts($entityMetadata->getCacheContexts());
            $result->addCacheTags($entityMetadata->getCacheTags());
            //$result = $result->merge($entityMetadata)->merge($accessMetadata);

            if ($access->isAllowed()) {
              $entity_output = $this->renderEntityEmbedDisplayPlugin(
                $entity,
                $context['data-entity-embed-display'],
                $context['data-entity-embed-settings'],
                $context
              );
            }
          }
        }
        catch(\Exception $e) {
          watchdog_exception('entity_embed', $e);
        }

        $this->replaceDomNodeContent($node, $entity_output);
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
   * Replace the contents of a DOMNode.
   *
   * @param \DOMNode $node
   *   A DOMNode or DOMElement object.
   * @param string $content
   *   The text or HTML that will replace the contents of $node.
   */
  protected function replaceDomNodeContent(\DOMNode $node, $content) {
    if (empty($content)) {
      $node->parentNode->removeChild($node);
    }
    else {
      // Load the contents into a new DOMDocument and retrieve the element.
      $replacement_node = Html::load($content)->getElementsByTagName('body')
        ->item(0)
        ->childNodes
        ->item(0);

      // Import the updated DOMNode from the new DOMDocument into the original
      // one, importing also the child nodes of the replacment DOMNode.
      $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);
      $node->parentNode->replaceChild($replacement_node, $node);
    }
  }
}
