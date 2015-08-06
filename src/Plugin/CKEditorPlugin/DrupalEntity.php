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
use Drupal\entity_embed\Entity\EmbedButton;
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
   * All embed button configuration entities.
   *
   * An associative array that stores the description of all embed button
   * configuration entities keyed by the button id.
   *
   * @var array
   */
  protected $embedButtons;

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

    $this->embedButtons = $embed_button_query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')->get('entity_embed_button')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $buttons = array();

    foreach ($this->embedButtons as $embed_button) {
      $button = EmbedButton::load($embed_button);
      $buttons[$button->id()] = array(
        'id' => SafeMarkup::checkPlain($button->id()),
        'name' => SafeMarkup::checkPlain($button->label()),
        'label' => SafeMarkup::checkPlain($button->getButtonLabel()),
        'entity_type' => SafeMarkup::checkPlain($button->getEntityTypeMachineName()),
        'image' => $button->getButtonImage(),
      );
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
      'core/drupal.ajax',
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
