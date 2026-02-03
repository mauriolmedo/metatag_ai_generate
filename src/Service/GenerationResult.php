<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Service;

/**
 * Value object holding meta description generation results.
 *
 * Encapsulates the result of a meta description generation attempt,
 * providing a consistent interface for handling both successful
 * and failed generation outcomes.
 */
final class GenerationResult {

  /**
   * Whether the generation was successful.
   */
  private bool $success;

  /**
   * The generated meta description (if successful).
   */
  private string $description;

  /**
   * The error message (if failed).
   */
  private ?string $error;

  /**
   * Constructs a GenerationResult.
   *
   * @param bool $success
   *   Whether the generation was successful.
   * @param string $description
   *   The generated description (empty if failed).
   * @param string|null $error
   *   The error message (null if successful).
   */
  private function __construct(bool $success, string $description, ?string $error) {
    $this->success = $success;
    $this->description = $description;
    $this->error = $error;
  }

  /**
   * Creates a successful result with a generated description.
   *
   * @param string $description
   *   The generated meta description.
   *
   * @return self
   *   A success result object.
   */
  public static function success(string $description): self {
    return new self(TRUE, $description, NULL);
  }

  /**
   * Creates a failure result with an error message.
   *
   * @param string $error
   *   The error message explaining the failure.
   *
   * @return self
   *   A failure result object.
   */
  public static function failure(string $error): self {
    return new self(FALSE, '', $error);
  }

  /**
   * Checks if the generation was successful.
   *
   * @return bool
   *   TRUE if generation succeeded, FALSE otherwise.
   */
  public function isSuccess(): bool {
    return $this->success;
  }

  /**
   * Gets the generated meta description.
   *
   * @return string
   *   The generated description, or empty string if generation failed.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Gets the error message if generation failed.
   *
   * @return string|null
   *   The error message, or NULL if generation succeeded.
   */
  public function getError(): ?string {
    return $this->error;
  }

}
