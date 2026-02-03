#!/bin/bash
# Import all translations for metatag_ai_generate module
# Usage: ./import-translations.sh [language_code]
#   If language_code is provided, imports only that language
#   Otherwise, imports all available translations

set -e

# Define colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TRANSLATIONS_DIR="$SCRIPT_DIR/../translations"
MODULE_NAME="metatag_ai_generate"

# Available languages
LANGUAGES=("fr" "es" "de" "zh-hans" "ja")

# Function to import a single language
import_language() {
    local LANG=$1
    local PO_FILE="$TRANSLATIONS_DIR/${MODULE_NAME}.${LANG}.po"

    if [ ! -f "$PO_FILE" ]; then
        echo -e "${RED}✗${NC} File not found: $PO_FILE"
        return 1
    fi

    echo -e "${YELLOW}Importing ${LANG}...${NC}"

    if drush locale:import "$LANG" "$PO_FILE" --type=customized --override=all --autocreate-language; then
        echo -e "${GREEN}✓${NC} Successfully imported ${LANG}"
        return 0
    else
        echo -e "${RED}✗${NC} Failed to import ${LANG}"
        return 1
    fi
}

# Main script
echo "========================================="
echo " Metatag AI Generate - Translation Import"
echo "========================================="
echo ""

# Check if we're in a Drupal site
if ! drush status --fields=bootstrap 2>/dev/null | grep -q "Successful"; then
    echo -e "${RED}✗${NC} Error: Not in a Drupal site or Drush not available"
    exit 1
fi

# Check if a specific language was requested
if [ -n "$1" ]; then
    echo "Importing single language: $1"
    echo ""
    if import_language "$1"; then
        echo ""
        echo -e "${GREEN}✓${NC} Import completed!"
    else
        echo ""
        echo -e "${RED}✗${NC} Import failed!"
        exit 1
    fi
else
    # Import all languages
    echo "Importing all available languages..."
    echo ""

    FAILED=0
    SUCCESS=0

    for LANG in "${LANGUAGES[@]}"; do
        if import_language "$LANG"; then
            ((SUCCESS++))
        else
            ((FAILED++))
        fi
        echo ""
    done

    # Summary
    echo "========================================="
    echo " Summary"
    echo "========================================="
    echo -e "${GREEN}✓${NC} Successfully imported: $SUCCESS"
    echo -e "${RED}✗${NC} Failed: $FAILED"

    if [ $FAILED -gt 0 ]; then
        exit 1
    fi
fi

# Clear cache
echo ""
echo -e "${YELLOW}Clearing Drupal cache...${NC}"
if drush cr; then
    echo -e "${GREEN}✓${NC} Cache cleared successfully"
else
    echo -e "${RED}✗${NC} Failed to clear cache"
    exit 1
fi

echo ""
echo -e "${GREEN}✓${NC} All done!"
echo ""
echo "Next steps:"
echo "1. Visit /admin/config/regional/language to verify installed languages"
echo "2. Visit /admin/config/search/metatag-ai-generate to test translated interface"
echo "3. Change site default language to test different translations"
