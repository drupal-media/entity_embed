<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Embed entity displays for image field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "image",
 *   label = @Translation("Image"),
 *   entity_types = {"file"},
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "image",
 *   provider = "image"
 * )
 */
class ImageFieldFormatter extends FileFieldFormatter {

  /**
    * The image factory.
    *
    * @var \Drupal\Core\Image\ImageFactory
    */
    protected $imageFactory;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_plugin_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, FormatterPluginManager $formatter_plugin_manager, TypedDataManager $typed_data_manager, ImageFactory $image_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $formatter_plugin_manager, $typed_data_manager);
    $this->imageFactory = $image_factory;
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
        $container->get('plugin.manager.field.formatter'),
        $container->get('typed_data_manager'),
        $container->get('image.factory')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = BaseFieldDefinition::create('image');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(BaseFieldDefinition $definition) {
    $value = parent::getFieldValue($definition);
    // File field support descriptions, but images do not.
    unset($value['description']);
    $value += array_intersect_key($this->getAttributeValues(), array('alt' => '', 'title' => ''));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    if ($this->hasContextValue('entity')) {
      $uri = $this->getContextValue('entity')->getFileUri();
      $image = $this->imageFactory->get($uri);
      return $image->isValid();
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // File field support descriptions, but images do not.
    unset($form['description']);

    // Ensure that the 'Link image to: Content' setting is not available.
    if ($this->getDerivativeId() == 'image') {
      unset($form['image_link']['#options']['content']);
    }

    // Add support for editing the alternate and title text attributes.
    $form['alt'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Alternate text'),
      '#default_value' => $this->getAttributeValue('alt', ''),
      '#description' => $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      '#parents' => array('attributes', 'alt'),
      // @see http://www.gawds.org/show.php?contentid=28
      '#maxlength' => 512,
    );
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->getAttributeValue('title', ''),
      '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#parents' => array('attributes', 'title'),
      '#maxlength' => 1024,
    );

    return $form;
  }

}
