# Contributing to Metatag AI Generate

Thank you for your interest in contributing! 🎉

## How to Contribute

### Reporting Bugs

1. Check [existing issues](https://github.com/elektrorl/metatag_ai_generate/issues) first
2. Create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - Drupal version, PHP version, AI provider used
   - Error messages from **Reports** → **Recent log messages**

### Suggesting Features

1. Open an issue with the `enhancement` label
2. Describe the feature and use case
3. Explain why it would benefit users

### Pull Requests

1. **Fork** the repository
2. **Create a branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes** following Drupal coding standards
4. **Test your code**:
   ```bash
   # Run PHPCS
   phpcs --standard=Drupal,DrupalPractice .

   # Run PHPUnit tests
   phpunit tests/
   ```
5. **Commit** with descriptive messages
6. **Push** and create a Pull Request

### Coding Standards

- Follow [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- Use dependency injection (no `\Drupal::service()`)
- Add PHPDoc comments
- Write unit/kernel tests for new features

### Translation Contributions

Help translate the module into more languages!

1. Copy `translations/metatag_ai_generate.pot`
2. Translate to your language using Poedit or similar
3. Save as `metatag_ai_generate.[lang-code].po`
4. Submit a PR

## Development Setup

```bash
# Clone the repo
git clone https://github.com/elektrorl/metatag_ai_generate.git
cd metatag_ai_generate

# Install dependencies (in a Drupal site)
composer require drupal/metatag drupal/ai drupal/key

# Enable the module
drush en metatag_ai_generate -y
```

## Questions?

Open a [Discussion](https://github.com/elektrorl/metatag_ai_generate/discussions) or comment on an issue!
