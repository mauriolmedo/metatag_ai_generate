<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service to interact with AI providers for meta tag generation.
 */
class AiProviderService {

  /**
   * The AI provider plugin manager.
   */
  protected AiProviderPluginManager $aiProvider;

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a new AiProviderService.
   *
   * @param \Drupal\ai\AiProviderPluginManager $ai_provider
   *   The AI provider plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    AiProviderPluginManager $ai_provider,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->aiProvider = $ai_provider;
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the configured AI provider and model.
   *
   * @return array{provider: \Drupal\ai\Plugin\ProviderProxy|null, model: string}
   *   Array with 'provider' (ProviderProxy or null) and 'model' (model ID).
   */
  public function getConfiguredProvider(): array {
    $config = $this->configFactory->get('metatag_ai_generate.settings');
    $provider_option = $config->get('default_provider');

    if (empty($provider_option)) {
      return ['provider' => NULL, 'model' => ''];
    }

    $provider = $this->aiProvider->loadProviderFromSimpleOption($provider_option);
    $model = $this->aiProvider->getModelNameFromSimpleOption($provider_option);

    return ['provider' => $provider, 'model' => $model];
  }

  /**
   * Checks if an AI provider is configured and available.
   *
   * @return bool
   *   TRUE if provider is configured and usable.
   */
  public function hasConfiguredProvider(): bool {
    $config = $this->getConfiguredProvider();
    return $config['provider'] !== NULL;
  }

  /**
   * Generates text using the configured AI provider.
   *
   * @param string $prompt
   *   The user prompt to send.
   * @param string $system_prompt
   *   Optional system prompt for context.
   *
   * @return string
   *   The generated text response.
   *
   * @throws \RuntimeException
   *   When no provider is configured.
   * @throws \Exception
   *   When the API call fails.
   */
  public function generateText(string $prompt, string $system_prompt = ''): string {
    $config = $this->getConfiguredProvider();

    if ($config['provider'] === NULL) {
      throw new \RuntimeException('No AI provider configured. Please configure a provider in the module settings.');
    }

    $messages = new ChatInput([
      new ChatMessage('user', $prompt),
    ]);

    if (!empty($system_prompt)) {
      $messages->setSystemPrompt($system_prompt);
    }

    /** @var \Drupal\ai\OperationType\Chat\ChatOutput $response */
    $response = $config['provider']->chat($messages, $config['model'], ['metatag_ai_generate']);

    return $response->getNormalized()->getText();
  }

  /**
   * Gets available provider/model options for forms.
   *
   * @return array
   *   Options array suitable for select form element.
   */
  public function getProviderOptions(): array {
    return $this->aiProvider->getSimpleProviderModelOptions('chat', TRUE, TRUE);
  }

}
