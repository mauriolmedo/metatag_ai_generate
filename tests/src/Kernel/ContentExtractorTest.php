<?php

declare(strict_types=1);

namespace Drupal\Tests\metatag_ai_generate\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests ContentExtractor service.
 *
 * @group metatag_ai_generate
 * @coversDefaultClass \Drupal\metatag_ai_generate\Service\ContentExtractor
 */
class ContentExtractorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'metatag_ai_generate',
  ];

  /**
   * The content extractor service.
   *
   * @var \Drupal\metatag_ai_generate\Service\ContentExtractorInterface
   */
  protected $contentExtractor;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field', 'filter']);

    // Create article content type.
    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();

    $this->contentExtractor = $this->container->get('metatag_ai_generate.content_extractor');
  }

  /**
   * Tests text extraction from a node.
   *
   * @covers ::extractText
   * @covers ::htmlToText
   */
  public function testExtractText(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
      'body' => [
        'value' => '<p>This is <strong>test content</strong> with HTML tags.</p>',
        'format' => 'plain_text',
      ],
    ]);
    $node->save();

    $text = $this->contentExtractor->extractText($node);

    $this->assertStringContainsString('Test Article', $text);
    $this->assertStringContainsString('test content', $text);
    // HTML tags should be stripped.
    $this->assertStringNotContainsString('<p>', $text);
    $this->assertStringNotContainsString('<strong>', $text);
  }

  /**
   * Tests whitespace normalization.
   *
   * @covers ::htmlToText
   */
  public function testWhitespaceNormalization(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
      'body' => [
        'value' => "Line 1\n\n\nLine 2    with    spaces",
        'format' => 'plain_text',
      ],
    ]);
    $node->save();

    $text = $this->contentExtractor->extractText($node);

    // Multiple spaces/newlines should be normalized to single space.
    $this->assertStringNotContainsString('   ', $text);
    $this->assertStringNotContainsString("\n\n", $text);
  }

  /**
   * Tests HTML entity decoding.
   *
   * @covers ::htmlToText
   */
  public function testHtmlEntityDecoding(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
      'body' => [
        'value' => 'Text with &amp; &lt; &gt; entities',
        'format' => 'plain_text',
      ],
    ]);
    $node->save();

    $text = $this->contentExtractor->extractText($node);

    // HTML entities should be decoded.
    $this->assertStringContainsString('&', $text);
    $this->assertStringContainsString('<', $text);
    $this->assertStringContainsString('>', $text);
  }

}
