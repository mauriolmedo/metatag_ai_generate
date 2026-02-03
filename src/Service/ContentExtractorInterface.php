<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

use Drupal\node\NodeInterface;

/**
 * Interface for extracting text content from nodes.
 *
 * This service extracts clean text from a node's rendered "full" view mode,
 * removing all HTML tags and normalizing whitespace. The resulting text is
 * suitable for use as input to AI/LLM services for meta description generation.
 */
interface ContentExtractorInterface {

  /**
   * Extracts clean text from a node's rendered full view mode.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity to extract text from.
   *
   * @return string
   *   The extracted plain text content, with HTML removed and whitespace
   *   normalized. Returns empty string if node has no renderable content.
   */
  public function extractText(NodeInterface $node): string;

}
