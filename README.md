<div align="center">

# Metatag AI Generate

### AI-Powered SEO Meta Description Generator for Drupal

*Automatically generate optimized meta descriptions using cutting-edge AI models directly from your Drupal content editor*

[![Drupal](https://img.shields.io/badge/Drupal-10.2%2B%20%7C%2011-0678BE?style=for-the-badge&logo=drupal&logoColor=white)](https://www.drupal.org)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0+-green?style=for-the-badge)](LICENSE)

[Features](#features) • [Installation](#installation) • [Configuration](#configuration) • [Usage](#usage) • [Troubleshooting](#troubleshooting) • [Multilingual Support](#multilingual-support)

---

</div>

## Why Metatag AI Generate?

Writing compelling, SEO-optimized meta descriptions is time-consuming and requires expertise. **Metatag AI Generate** eliminates this burden by intelligently analyzing your content and generating professional meta descriptions in seconds.

### Comparison with Metatag AI

There is another similar module called [Metatag AI](https://www.drupal.org/project/metatag_ai).

**Metatag AI Generate**:
- **Manual generation**: You click a "Generate with AI" button when YOU want
- **Scope**: Generates ONLY meta descriptions (not titles)
- **Control**: Full control - generate, review, edit before saving
- **Personas**: Customizable AI writing style per site
- **Bundle-specific**: Enable only for specific content types

**Choose this module if you want:**
- More control over the generation process
- To review and edit AI suggestions before saving
- To generate only descriptions (keep manual titles)
- To customize the AI writing style

**Choose Metatag AI if you want:**
- Fully automatic generation on save
- Both titles and descriptions generated together

### Key Features

- **Lightning Fast**: Generate SEO-optimized descriptions in under 2 seconds
- **Smart Content Analysis**: Automatically extracts and analyzes content from rendered node output
- **Multi-Provider Support**: Works with Anthropic Claude, Mistral AI, OpenRouter, OpenAI, and more
- **Customizable Personas**: Define your writing style (professional, creative, technical, etc.)
- **SEO-Compliant**: Generates 155-200 character descriptions with key information front-loaded in the first 160 characters
- **Bundle-Specific**: Enable only for content types that need it
- **Secure**: API keys managed through Drupal's Key module

---

## Installation

### Step 1: Install Required Modules

1. **Via Composer** (recommended):
   ```bash
   composer require drupal/metatag drupal/ai drupal/key
   composer require drupal/metatag_ai_generate
   ```

2. **Or download manually** from Drupal.org

### Step 2: Enable the Modules

1. Go to **Extend** (`/admin/modules`)
2. Search for **"Metatag AI Generate"**
3. Check the box and click **Install**

Or use command line:
```bash
drush en metatag_ai_generate -y
```

### Step 3: Install an AI Provider & Get API Key

Choose one provider and install its Drupal module:

1. **Install the provider module** via Composer:
  ```bash
  # Anthropic Claude
  composer require drupal/ai_provider_anthropic

  # Mistral AI
  composer require drupal/ai_provider_mistral

  # OpenRouter
  composer require drupal/ai_provider_openrouter

  # OpenAI
  composer require drupal/ai_provider_openai
  ```

2. **Get an API key** from your chosen provider:
   - **Anthropic Claude**: [console.anthropic.com](https://console.anthropic.com/)
   - **Mistral AI**: [console.mistral.ai](https://console.mistral.ai/)
   - **OpenRouter**: [openrouter.ai](https://openrouter.ai/) (access to 100+ models)
   - **OpenAI**: [platform.openai.com](https://platform.openai.com/)

---

## Configuration

### Configure API Key

1. Go to **Configuration** → **System** → **Keys** (`/admin/config/system/keys`)
2. Click **+ Add key**
3. Fill in:
   - **Key name**: `anthropic_api_key` (or your provider name)
   - **Key type**: Authentication
   - **Key provider**: Configuration
   - **Key value**: Paste your API key
4. Click **Save**

### Configure Metatag AI Generate

Navigate to: **Configuration** → **Search and metadata** → **Metatag AI Generate**
(`/admin/config/search/metatag-ai-generate`)

<table>
<tr>
<td><strong>Setting</strong></td>
<td><strong>What to do</strong></td>
</tr>
<tr>
<td><strong>Enable Module</strong></td>
<td>Check to activate AI generation</td>
</tr>
<tr>
<td><strong>AI Provider</strong></td>
<td>Select your provider (Anthropic, Mistral, OpenRouter, or OpenAI)</td>
</tr>
<tr>
<td><strong>API Key</strong></td>
<td>Select the key you created above</td>
</tr>
<tr>
<td><strong>Persona</strong></td>
<td>Define your writing style, e.g.:<br>
• <code>"a professional content writer specializing in SEO"</code><br>
• <code>"a creative copywriter focused on user engagement"</code><br>
• <code>"an e-commerce content specialist"</code>
</td>
</tr>
<tr>
<td><strong>Enabled Content Types</strong></td>
<td>Check content types where you want AI generation (e.g., Article, Page)</td>
</tr>
</table>

Click **Save configuration**

---

## Usage

### Generate Meta Descriptions

1. **Create or edit content** (e.g., an Article or Page)
2. **Scroll to the Metatag section** (usually in the right sidebar)
3. **Click the "Generate with AI" button**
4. **Wait 1-2 seconds** while AI analyzes your content
5. **Review the generated description** in the Description field
6. **Edit if needed**, then click **Save**

That's it! Your content now has an SEO-optimized meta description.

### How It Works

**Content Extraction:**
- The module renders your node in "full" view mode
- Extracts plain text from the rendered output (max 5000 characters)
- Includes all visible content: title, body, custom fields, and any displayed data
- Strips HTML tags and normalizes whitespace

**AI Generation:**
- Sends the extracted content to your configured AI provider
- Uses your custom persona to set the writing style
- Generates descriptions between **155-200 characters**
- **Front-loads** the most important information in the first **160 characters** (guaranteed visible on all devices)
- Characters 161-200 can add additional context (may be truncated on mobile)

**Best Practice (2026):**
- Google displays approximately 155-160 characters on all devices
- Up to 200 characters accepted for additional context
- Always put key information first for maximum visibility

---

## Troubleshooting

### "Generate with AI" button doesn't appear

**Solution:**
1. Go to **Configuration** → **Search and metadata** → **Metatag AI Generate**
2. Check that the module is **enabled** (checkbox at top)
3. Check that your content type is selected in **Enabled Content Types**
4. Clear Drupal cache: **Configuration** → **Development** → **Performance** → **Clear all caches**

---

### "No AI provider configured" error

**Solution:**
1. Go to **Configuration** → **Search and metadata** → **Metatag AI Generate**
2. Make sure you selected an **AI Provider** from the dropdown
3. Make sure you selected an **API Key** (must be created first in Keys module)
4. Click **Save configuration**

---

### AI generation fails or returns an error

**Check your API key:**
1. Go to **Configuration** → **System** → **Keys**
2. Edit your API key
3. Make sure the key value is correct (no extra spaces)

**Check your provider's dashboard:**
- Verify your API key is active
- Check you have available credits/quota
- Check for any service outages

**Still not working?**
- Check **Reports** → **Recent log messages** for detailed error messages
- Contact your AI provider's support if the error is on their side

---

## Multilingual Support

Metatag AI Generate is available in **6 languages** with complete professional translations:

| Language   | Code      | Status     |
| ---------- | --------- | ---------- |
| 🇬🇧 English  | `en`      | ✅ Native   |
| 🇫🇷 Français | `fr`      | ✅ Complete |
| 🇪🇸 Español  | `es`      | ✅ Complete |
| 🇩🇪 Deutsch  | `de`      | ✅ Complete |
| 🇨🇳 简体中文 | `zh-hans` | ✅ Complete |
| 🇯🇵 日本語   | `ja`      | ✅ Complete |

---

## License

This module is licensed under the **GNU General Public License v2.0 or later**.

---

<div align="center">

### If you find this module helpful, please star it on GitHub!



[Report a Bug](https://github.com/elektrorl/metatag_ai_generate/issues) • [Request Feature](https://github.com/elektrorl/metatag_ai_generate/issues) • [Discussions](https://github.com/elektrorl/metatag_ai_generate/discussions)

</div>


