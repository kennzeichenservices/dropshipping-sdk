#!/bin/bash
set -e

# ---------------------------------------------------------------------------
# Release script — reads version from composer.json, generates CHANGELOG.md,
# commits and tags.
# Usage: composer release
# Requires: git-cliff (brew install git-cliff), jq (brew install jq)
# ---------------------------------------------------------------------------

# Check jq is installed
if ! command -v jq &>/dev/null; then
    echo "Error: jq is not installed."
    echo "       Install with: brew install jq"
    exit 1
fi

VERSION=$(jq -r '.version' composer.json)
TAG="v${VERSION}"

# Validate version format (semver)
if ! echo "$VERSION" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+$'; then
    echo "Error: composer.json must contain a semver version (e.g. 1.8.5), got: '$VERSION'"
    exit 1
fi

# Check git-cliff is installed
if ! command -v git-cliff &>/dev/null; then
    echo "Error: git-cliff is not installed."
    echo "       Install with: brew install git-cliff"
    exit 1
fi

# Refuse to run with uncommitted changes (other than composer.json / CHANGELOG.md)
DIRTY=$(git status --porcelain | grep -v '^[AM ]. composer.json$' | grep -v '^[AM ]. CHANGELOG.md$' || true)
if [ -n "$DIRTY" ]; then
    echo "Error: Working tree has uncommitted changes. Commit or stash them first."
    echo "$DIRTY"
    exit 1
fi

# Refuse if tag already exists
if git rev-parse "$TAG" &>/dev/null; then
    echo "Error: Tag $TAG already exists. Update the version in composer.json to a new value."
    exit 1
fi

echo "Preparing release $TAG..."

# Generate / update CHANGELOG.md
git-cliff --tag "$TAG" -o CHANGELOG.md

# Stage composer.json and CHANGELOG.md
git add composer.json CHANGELOG.md

# Create release commit
git commit -m "chore: release $TAG"

# Create annotated tag
git tag -a "$TAG" -m "Release $TAG"

echo ""
echo "Release $TAG ready."

git push && git push --tags
echo "Pushed with: git push && git push --tags"
