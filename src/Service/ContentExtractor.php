<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;

/**
 * Extracts clean text content from node entities.
 *
 * This service renders a node in "full" view mode and extracts the plain text
 * content by removing HTML tags, decoding entities, and normalizing whitespace.
 */
class ContentExtractor implements ContentExtractorInterface {

  /**
   * Maximum characters to extract (to limit API costs).
   *
   * @var int
   */
  public const MAX_TEXT_LENGTH = 5000;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Constructs a ContentExtractor service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    RendererInterface $renderer,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function extractText(NodeInterface $node): string {
    // Get the view builder for node entities.
    $viewBuilder = $this->entityTypeManager->getViewBuilder('node');

    // Build the render array for the node in "full" view mode.
    $build = $viewBuilder->view($node, 'full');

    // Render to HTML string without caching side effects.
    $html = (string) $this->renderer->renderPlain($build);

    // Convert HTML to clean text.
    $text = $this->htmlToText($html);

    // Limit text length to avoid excessive API costs.
    if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
      $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH) . '...';
    }

    return $text;
  }

  /**
   * Converts HTML content to plain text.
   *
   * @param string $html
   *   The HTML content to convert.
   *
   * @return string
   *   The plain text content with tags removed and whitespace normalized.
   */
  protected function htmlToText(string $html): string {
    // Remove HTML tags.
    $text = strip_tags($html);

    // Decode HTML entities.
    $text = Html::decodeEntities($text);

    // Normalize whitespace: replace multiple spaces/newlines with single space.
    $text = preg_replace('/\s+/', ' ', $text);

    // Trim leading and trailing whitespace.
    return trim($text);
  }

}
