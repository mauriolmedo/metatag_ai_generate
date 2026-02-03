<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\Exception\AiRequestErrorException;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Generates meta descriptions using AI/LLM providers.
 *
 * This service orchestrates the generation of SEO-optimized meta descriptions
 * by extracting content from nodes and sending it to a configured AI provider.
 * It handles error cases gracefully and returns structured results.
 */
class MetaDescriptionGenerator implements MetaDescriptionGeneratorInterface {

  /**
   * The AI provider plugin manager.
   */
  protected AiProviderPluginManager $aiProvider;

  /**
   * The content extractor service.
   */
  protected ContentExtractorInterface $contentExtractor;

  /**
   * The config factory service.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The logger for this channel.
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a MetaDescriptionGenerator.
   *
   * @param \Drupal\ai\AiProviderPluginManager $ai_provider
   *   The AI provider plugin manager.
   * @param \Drupal\metatag_ai_generate\Service\ContentExtractorInterface $content_extractor
   *   The content extractor service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(
    AiProviderPluginManager $ai_provider,
    ContentExtractorInterface $content_extractor,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->aiProvider = $ai_provider;
    $this->contentExtractor = $content_extractor;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('metatag_ai_generate');
  }

  /**
   * {@inheritdoc}
   */
  public function generate(NodeInterface $node): GenerationResult {
    $config = $this->configFactory->get('metatag_ai_generate.settings');

    // Check if generation is enabled.
    if (!$config->get('enabled')) {
      return GenerationResult::failure('AI meta description generation is disabled.');
    }

    // Check if any provider is available for chat operations.
    if (!$this->aiProvider->hasProvidersForOperationType('chat', TRUE)) {
      return GenerationResult::failure('No AI provider configured for chat.');
    }

    // Get the configured provider from our settings.
    $provider_option = $config->get('default_provider');
    if (empty($provider_option)) {
      return GenerationResult::failure('No default AI provider configured.');
    }

    // Parse provider and model from the combined option.
    $provider = $this->aiProvider->loadProviderFromSimpleOption($provider_option);
    $model = $this->aiProvider->getModelNameFromSimpleOption($provider_option);

    if ($provider === NULL) {
      return GenerationResult::failure('Configured AI provider is not available.');
    }

    // Extract content from the node.
    $text = $this->contentExtractor->extractText($node);
    if (empty($text)) {
      return GenerationResult::failure('No content available to generate description from.');
    }

    // Build the prompt with persona.
    $persona = $config->get('persona') ?: 'a professional content writer';
    // SEO Best Practice 2026: Aim for 155-160 chars
    // (guaranteed visible on all devices).
    // Up to 200 chars accepted for additional context
    // (may be truncated on mobile).
    // Front-load key information in first 160 characters.
    $system_prompt = "You are $persona. Your task is to write meta "
      . "descriptions for SEO that are 155-200 characters long. "
      . "FRONT-LOAD the most important information in the first "
      . "160 characters (guaranteed visible). You can extend to "
      . "200 chars to add context. Example: 'Discover how artificial "
      . "intelligence transforms modern business operations with "
      . "machine learning, automation, and data analytics. Practical "
      . "implementation strategies for 2026.' (183 chars). Return ONLY "
      . "the description, no formatting.";

    $user_prompt = "Write a meta description between 155-200 characters "
      . "for this content. IMPORTANT: Front-load key information in "
      . "the first 160 characters. Make it engaging and informative:"
      . "\n\n$text";

    $input = new ChatInput([
      new ChatMessage('user', $user_prompt),
    ]);
    $input->setSystemPrompt($system_prompt);

    // Call the LLM with error handling.
    try {
      /** @var \Drupal\ai\OperationType\Chat\ChatOutput $response */
      $response = $provider->chat($input, $model, ['metatag_ai_generate']);
      $message = $response->getNormalized();
      $description = trim($message->getText());

      // Remove surrounding quotes that LLMs often add.
      $description = trim($description, '"\'');

      // Remove common prefixes that LLMs add.
      $prefixes_to_remove = [
        '/^\*\*Meta Description[^:]*:\*\*\s*/i',
        '/^Meta Description[^:]*:\s*/i',
        '/^\*\*Description[^:]*:\*\*\s*/i',
        '/^Description[^:]*:\s*/i',
      ];
      foreach ($prefixes_to_remove as $pattern) {
        $description = preg_replace($pattern, '', $description);
      }
      $description = trim($description);

      // Truncate if too long (max 200 characters for meta description).
      // Google typically displays 155-160 chars,
      // but up to 200 provides useful context.
      if (mb_strlen($description) > 200) {
        // Find the last complete word within 200 chars.
        $description = mb_substr($description, 0, 197);
        $last_space = mb_strrpos($description, ' ');
        if ($last_space !== FALSE && $last_space > 180) {
          $description = mb_substr($description, 0, $last_space);
        }
        $description .= '...';
      }

      if (empty($description)) {
        return GenerationResult::failure('AI returned an empty response.');
      }

      return GenerationResult::success($description);
    }
    catch (AiRequestErrorException $e) {
      $this->logger->error('AI request error: @message', ['@message' => $e->getMessage()]);
      return GenerationResult::failure('AI request failed: ' . $e->getMessage());
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error during meta description generation: @message', [
        '@message' => $e->getMessage(),
      ]);
      return GenerationResult::failure('An unexpected error occurred.');
    }
  }

}
