<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

use Drupal\node\NodeInterface;

/**
 * Interface for meta description generation via LLM.
 *
 * This service generates SEO-optimized meta descriptions by sending node
 * content to a configured AI provider. The generation considers the site's
 * persona configuration to produce contextually appropriate descriptions.
 */
interface MetaDescriptionGeneratorInterface {

  /**
   * Generates a meta description for the given node.
   *
   * Extracts the node's content and sends it to the configured AI provider
   * to generate an SEO-optimized meta description of 150-160 characters.
   * The generation respects the persona configuration set by administrators.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to generate a meta description for.
   *
   * @return \Drupal\metatag_ai_generate\Service\GenerationResult
   *   A result object indicating success or failure with appropriate data.
   *   On success, contains the generated description.
   *   On failure, contains an error message.
   */
  public function generate(NodeInterface $node): GenerationResult;

}
