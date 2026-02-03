<?php

declare(strict_types=1);

namespace Drupal\Tests\metatag_ai_generate\Unit;

use Drupal\metatag_ai_generate\Service\GenerationResult;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for GenerationResult value object.
 *
 * @group metatag_ai_generate
 * @coversDefaultClass \Drupal\metatag_ai_generate\Service\GenerationResult
 */
class GenerationResultTest extends UnitTestCase {

  /**
   * Tests successful result creation.
   *
   * @covers ::success
   * @covers ::isSuccess
   * @covers ::getDescription
   * @covers ::getError
   */
  public function testSuccessResult(): void {
    $description = 'Generated meta description for testing purposes.';
    $result = GenerationResult::success($description);

    $this->assertTrue($result->isSuccess());
    $this->assertEquals($description, $result->getDescription());
    $this->assertNull($result->getError());
  }

  /**
   * Tests failure result creation.
   *
   * @covers ::failure
   * @covers ::isSuccess
   * @covers ::getDescription
   * @covers ::getError
   */
  public function testFailureResult(): void {
    $error = 'AI provider not configured';
    $result = GenerationResult::failure($error);

    $this->assertFalse($result->isSuccess());
    $this->assertEquals('', $result->getDescription());
    $this->assertEquals($error, $result->getError());
  }

  /**
   * Tests that success result has no error.
   *
   * @covers ::success
   * @covers ::getError
   */
  public function testSuccessHasNoError(): void {
    $result = GenerationResult::success('Description');

    $this->assertNull($result->getError());
  }

  /**
   * Tests that failure result has empty description.
   *
   * @covers ::failure
   * @covers ::getDescription
   */
  public function testFailureHasEmptyDescription(): void {
    $result = GenerationResult::failure('Error message');

    $this->assertEquals('', $result->getDescription());
  }

}
