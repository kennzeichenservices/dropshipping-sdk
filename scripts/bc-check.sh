#!/usr/bin/env bash
#
# Backward-compatibility check against a released version.
#
# Answers the question "if a customer upgrades, does the SDK still behave the same?"
# in two ways:
#
#   1. Public API surface — every public class, property, method signature and enum
#      case, diffed. Catches signature changes a consumer's code would break on.
#   2. Hydration behaviour — a corpus of realistic payloads run through both versions'
#      DTOs, results compared field by field. Catches silent behaviour changes that
#      no unit test happens to cover.
#
# Usage:
#   composer bc-check              # compare against the latest git tag
#   composer bc-check -- v2.3.20   # compare against a specific ref
#
# Exits non-zero when a hydration regression is found.

set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"
TOOLS="$ROOT/scripts/bc-check"

REF="${1:-$(git tag --sort=-v:refname | head -1)}"

if [ -z "$REF" ]; then
    echo "No git tag found and no ref given. Usage: composer bc-check -- <ref>" >&2
    exit 2
fi

if ! git rev-parse --verify --quiet "$REF^{commit}" >/dev/null; then
    echo "Unknown git ref: $REF" >&2
    exit 2
fi

WORK="$(mktemp -d)"
BASE="$WORK/baseline"

cleanup() {
    git worktree remove --force "$BASE" >/dev/null 2>&1 || true
    rm -rf "$WORK"
}
trap cleanup EXIT

echo "Comparing working tree against $REF ($(git rev-parse --short "$REF"))"
echo

git worktree add --detach "$BASE" "$REF" >/dev/null 2>&1

# --- 1. public API surface -----------------------------------------------------
php "$TOOLS/api-dump.php" "$BASE/src" >"$WORK/api-old.txt" 2>/dev/null
php "$TOOLS/api-dump.php" "$ROOT/src" >"$WORK/api-new.txt" 2>/dev/null

echo "Public API surface"

if diff -u "$WORK/api-old.txt" "$WORK/api-new.txt" >"$WORK/api.diff"; then
    echo "  unchanged"
else
    removed=$(grep -cE '^-  ' "$WORK/api.diff" || true)
    added=$(grep -cE '^\+  ' "$WORK/api.diff" || true)
    echo "  $removed line(s) removed or changed, $added added — full diff below"
    echo
    grep -E '^[-+]' "$WORK/api.diff" | grep -vE '^(\+\+\+|---)' | grep -vE '^[-+]\s*$' | sed 's/^/    /'
fi

echo

# --- 2. hydration behaviour ----------------------------------------------------
php "$TOOLS/hydration-dump.php" "$BASE/src" "$TOOLS/corpus.php" >"$WORK/hydration-old.jsonl" 2>/dev/null
php "$TOOLS/hydration-dump.php" "$ROOT/src" "$TOOLS/corpus.php" >"$WORK/hydration-new.jsonl" 2>/dev/null

echo "Hydration behaviour"
php "$TOOLS/compare.php" "$WORK/hydration-old.jsonl" "$WORK/hydration-new.jsonl"
