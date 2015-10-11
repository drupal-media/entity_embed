<?php

/**
 * @file
 * Contains \Drupal\entity_embed\FieldWidgetBase.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\WidgetPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FieldWidgetBase extends EntityEmbedWidgetBase {

  /**
   * The widget formatter plugin manager.
   *
   * @var \Drupal\Core\Field\WidgetPluginManager
   */
  protected $widgetManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\BaseFieldDefinition
   */
  protected $fieldDefinition;

  /**
   * The field widget.
   *
   * @var \Drupal\Core\Field\WidgetInterface
   */
  protected $fieldWidget;

  /**
   * Constructs a FieldFormatterEntityEmbedDisplayBase object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Field\WidgetPluginManager $widget_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, WidgetPluginManager $widget_manager, TypedDataManager $typed_data_manager) {
    $this->widgetManager = $widget_manager;
    $this->setConfiguration($configuration);
    $this->setEntityManager($entity_manager);
    $this->typedDataManager = $typed_data_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager);
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
      $container->get('plugin.manager.field.widget'),
      $container->get('typed_data_manager')
    );
  }

  /**
   * Get the FieldDefinition object required to render this field's formatter.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The field definition.
   *
   * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase::build()
   */
  public function getFieldDefinition() {
    if (!isset($this->fieldDefinition)) {
      $field_type = $this->getPluginDefinition()['field_type'];
      $this->fieldDefinition = BaseFieldDefinition::create($field_type);
      // Ensure the field name is unique for each display plugin instance.
      static $index = 0;
      $this->fieldDefinition->setName('_entity_embed_widget_' . $index++);
    }
    return $this->fieldDefinition;
  }

  /**
   * Get the field value required to pass into the field formatter.
   *
   * @return mixed
   *   The field value.
   */
  abstract public function getFieldValue();

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    $definition = $this->widgetManager->getDefinition($this->getDerivativeId());
    return $definition['class']::isApplicable($this->getFieldDefinition());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Create a temporary node object to which our fake field value can be
    // added.
    $node = Node::create(array('type' => '_entity_embed'));

    $definition = $this->getFieldDefinition();

    /* @var \Drupal\Core\Field\FieldItemListInterface $items $items */
    // Create a field item list object, 1 is the value, array('target_id' => 1)
    // would work too, or multiple values. 1 is passed down from the list to the
    // field item, which knows that an integer is the ID.
    $items = $this->typedDataManager->create(
      $definition,
      $this->getFieldValue($definition),
      $definition->getName(),
      $node->getTypedData()
    );

    $widget = $this->getFieldWidget();
    $form = $form_state = [];
    return $widget->formElement($items, 0, $form, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return $this->widgetManager->getDefaultSettings($this->getDerivativeId());
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $this->getFieldWidget()->settingsForm($form, $form_state);
  }

  /**
   * Constructs a field widget.
   *
   * @return \Drupal\Core\Field\WidgetInterface
   *   The widget object.
   */
  public function getFieldWidget() {
    if (!isset($this->fieldWidget)) {
      $display = array(
        'type' => $this->getDerivativeId(),
        'settings' => $this->getConfiguration(),
        'label' => 'hidden',
      );

      // Create the formatter plugin. Will use the default formatter for that
      // field type if none is passed.
      $this->fieldWidget = $this->widgetManager->getInstance(
        array(
          'field_definition' => $this->getFieldDefinition(),
          'view_mode' => '_entity_embed',
          'configuration' => $display,
        )
      );
    }

    return $this->fieldWidget;
  }

  /**
   * Creates a new faux-field definition.
   *
   * @param string $type
   *   The type of the field.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   A new field definition.
   */
  protected function createFieldDefinition($type) {
    $definition = BaseFieldDefinition::create($type);
    static $index = 0;
    $definition->setName('_entity_embed_widget_' . $index++);
    return $definition;
  }
}
