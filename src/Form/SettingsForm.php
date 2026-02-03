<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Form;

use Drupal\ai\AiProviderPluginManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Metatag AI Generate settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The AI provider plugin manager.
   */
  protected AiProviderPluginManager $aiProvider;

  /**
   * The entity type bundle info service.
   */
  protected EntityTypeBundleInfoInterface $entityTypeBundleInfo;

  /**
   * The entity field manager.
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->aiProvider = $container->get('ai.provider');
    $instance->entityTypeBundleInfo = $container->get('entity_type.bundle.info');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'metatag_ai_generate_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['metatag_ai_generate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('metatag_ai_generate.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable AI meta tag generation'),
      '#description' => $this->t('When enabled, AI-powered meta description generation is available for content.'),
      '#default_value' => $config->get('enabled') ?? FALSE,
    ];

    // Get available providers that support chat operation.
    $provider_options = $this->aiProvider->getSimpleProviderModelOptions('chat', TRUE, TRUE);

    $form['default_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('AI Provider'),
      '#description' => $this->t('Select the AI provider and model to use for generating meta descriptions. Providers must be configured with API keys before they appear here.'),
      '#options' => $provider_options,
      '#default_value' => $config->get('default_provider') ?? '',
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Show message if no providers available.
    if (count($provider_options) <= 1) {
      $form['provider_warning'] = [
        '#type' => 'markup',
        '#markup' => '<div class="messages messages--warning">' .
        $this->t('No AI providers are configured. Please <a href=":url">configure an AI provider</a> with a valid API key first.', [
          ':url' => '/admin/config/ai/providers',
        ]) . '</div>',
        '#weight' => -10,
      ];
    }

    $form['persona'] = [
      '#type' => 'textarea',
      '#title' => $this->t('AI Persona'),
      '#description' => $this->t('Describe the persona the AI should adopt when generating meta descriptions. Example: "a professional content writer specializing in SEO for e-commerce"'),
      '#default_value' => $config->get('persona'),
      '#rows' => 3,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Build checkboxes for node bundles.
    $bundle_options = $this->getNodeBundleOptions();
    $form['enabled_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled Content Types'),
      '#description' => $this->t('Select which content types should have AI meta tag generation enabled. Leave all unchecked to enable for all content types.'),
      '#options' => $bundle_options,
      '#default_value' => $config->get('enabled_bundles') ?? [],
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Filter enabled_bundles to only include checked values.
    $enabled_bundles = array_filter($form_state->getValue('enabled_bundles'));
    $enabled_bundles = array_values($enabled_bundles);

    $this->config('metatag_ai_generate.settings')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('default_provider', $form_state->getValue('default_provider'))
      ->set('persona', $form_state->getValue('persona'))
      ->set('enabled_bundles', $enabled_bundles)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get node bundles that have metatag fields as options for checkboxes.
   *
   * @return array
   *   Array of bundle machine names to labels.
   */
  protected function getNodeBundleOptions(): array {
    $options = [];
    $bundles = $this->entityTypeBundleInfo->getBundleInfo('node');
    $metatag_bundles = $this->getMetatagEnabledBundles();

    foreach ($bundles as $bundle_id => $bundle_info) {
      // Only include bundles that have a metatag field.
      if (in_array($bundle_id, $metatag_bundles, TRUE)) {
        $options[$bundle_id] = $bundle_info['label'];
      }
    }

    return $options;
  }

  /**
   * Get all node bundles that have a metatag field attached.
   *
   * @return array
   *   Array of bundle machine names with metatag fields.
   */
  protected function getMetatagEnabledBundles(): array {
    $metatag_bundles = [];
    $bundles = $this->entityTypeBundleInfo->getBundleInfo('node');

    foreach (array_keys($bundles) as $bundle_id) {
      $fields = $this->entityFieldManager->getFieldDefinitions('node', $bundle_id);

      foreach ($fields as $field) {
        if ($field->getType() === 'metatag') {
          $metatag_bundles[] = $bundle_id;
          break;
        }
      }
    }

    return $metatag_bundles;
  }

}
