<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity.
 */

namespace Drupal\entity_embed\Plugin\CKEditorPlugin;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\embed\Entity\EmbedButton;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "drupalentity" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalentity",
 *   label = @Translation("Entity"),
 *   module = "entity_embed"
 * )
 */
class DrupalEntity extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The embed button query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $embedButtonQuery;

  /**
   * Constructs a Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryInterface $embed_button_query
   *   The entity query object for embed button.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryInterface $embed_button_query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->embedButtonQuery = $embed_button_query;
    $this->embedButtonQuery->condition('type_id', 'entity');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')->get('embed_button')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $buttons = array();

    if ($ids = $this->embedButtonQuery->execute()) {
      $embed_buttons = EmbedButton::loadMultiple($ids);
      foreach ($embed_buttons as $button) {
        $buttons[$button->id()] = array(
          'id' => $button->id(),
          'name' => SafeMarkup::checkPlain($button->label()),
          'label' => SafeMarkup::checkPlain($button->label()),
          'entity_type' => $button->getTypeSetting('entity_type'),
          'image' => $button->getIconUrl(),
        );
      }
    }

    return $buttons;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array(
      'embed/embed',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    $buttons = $this->getButtons();

    return array(
      'DrupalEntity_dialogTitleAdd' => t('Insert entity'),
      'DrupalEntity_dialogTitleEdit' => t('Edit entity'),
      'DrupalEntity_buttons' => $buttons,
    );
  }

}
