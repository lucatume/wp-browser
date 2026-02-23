#! /usr/bin/env bash

set -eu

destination_dir=${1:-}

if [ -z "$destination_dir" ]; then
    echo "Usage: $0 <destination_dir>"
    exit 1
fi

if [ ! -d "$destination_dir" ]; then
    echo "Error: destination directory '$destination_dir' does not exist."
    exit 1
fi

last_build_file=".last-35-build"

if [ ! -f "$last_build_file" ]; then
    echo "Error: $last_build_file not found. Cannot determine last transpiled commit."
    exit 1
fi

last_commit=$(cat "$last_build_file" | tr -d '[:space:]')
current_commit=$(git rev-parse HEAD)

if [ "$last_commit" = "$current_commit" ]; then
    echo "Already up to date (HEAD = $current_commit)."
    exit 0
fi

echo "Transpiling changes: $last_commit..$current_commit"

added_files=$(git diff --no-renames --name-only --diff-filter=A "$last_commit" "$current_commit" -- src/ includes/ tests/)
modified_files=$(git diff --no-renames --name-only --diff-filter=M "$last_commit" "$current_commit" -- src/ includes/ tests/)
deleted_files=$(git diff --no-renames --name-only --diff-filter=D "$last_commit" "$current_commit" -- src/ includes/ tests/)

if [ -z "$added_files" ] && [ -z "$modified_files" ] && [ -z "$deleted_files" ]; then
    echo "No relevant files changed in src/, includes/, or tests/."
    echo "$current_commit" > "$last_build_file"
    echo "Updated $last_build_file to $current_commit."
    exit 0
fi

[ -n "$added_files" ] && echo "Added files:" && echo "$added_files"
[ -n "$modified_files" ] && echo "Modified files:" && echo "$modified_files"
[ -n "$deleted_files" ] && echo "Deleted files:" && echo "$deleted_files"

all_changed_files=""
[ -n "$added_files" ] && all_changed_files="$added_files"
if [ -n "$modified_files" ]; then
    if [ -n "$all_changed_files" ]; then
        all_changed_files="$all_changed_files
$modified_files"
    else
        all_changed_files="$modified_files"
    fi
fi

cleanup() {
    echo "Cleaning up .build/ ..."
    rm -rf .build
}
trap cleanup EXIT

setup_build_dir() {
    local build_dir=$1
    [ -d "$build_dir" ] && rm -rf "$build_dir"
    mkdir -p "$build_dir"
    rsync -a --exclude-from=bin/build-35-exclusions.txt . "$build_dir"
    cd "$build_dir"
    rm -rf vendor composer.lock
    composer require --dev rector/rector:0.19.8 -W
    composer dump-autoload
    cd - > /dev/null
}

run_rector() {
    local build_dir=$1
    shift
    cd "$build_dir"
    vendor/bin/rector process --config=config/rector-35.php "$@"
    cd - > /dev/null
}

echo ""
echo "==> Setting up build directory .build/35 ..."
setup_build_dir .build/35

if [ -n "$all_changed_files" ]; then
    echo ""
    echo "==> Transpiling NEW versions of changed files ..."
    # shellcheck disable=SC2086
    run_rector .build/35 $all_changed_files

    mkdir -p .build/35-new
    echo "$all_changed_files" | while IFS= read -r file; do
        mkdir -p ".build/35-new/$(dirname "$file")"
        cp ".build/35/$file" ".build/35-new/$file"
    done
fi

if [ -n "$modified_files" ]; then
    echo ""
    echo "==> Replacing modified files with OLD versions and transpiling ..."

    echo "$modified_files" | while IFS= read -r file; do
        git show "$last_commit:$file" > ".build/35/$file"
    done

    # shellcheck disable=SC2086
    run_rector .build/35 $modified_files

    mkdir -p .build/35-old
    echo "$modified_files" | while IFS= read -r file; do
        mkdir -p ".build/35-old/$(dirname "$file")"
        cp ".build/35/$file" ".build/35-old/$file"
    done

    echo ""
    echo "==> Creating and applying patches for modified files ..."
    mkdir -p .build/patches
    patch_fail_marker=".build/.patch-failed"
    rm -f "$patch_fail_marker"
    echo "$modified_files" | while IFS= read -r file; do
        mkdir -p ".build/patches/$(dirname "$file")"
        patch_file=".build/patches/${file}.patch"

        diff -u ".build/35-old/$file" ".build/35-new/$file" > "$patch_file" || true

        if [ ! -s "$patch_file" ]; then
            echo "  [skip] $file (no transpilation difference)"
            continue
        fi

        dest_file="$destination_dir/$file"
        if [ ! -f "$dest_file" ]; then
            echo "  [warn] $file does not exist in destination, copying new version instead."
            mkdir -p "$destination_dir/$(dirname "$file")"
            cp ".build/35-new/$file" "$dest_file"
            continue
        fi

        echo "  [patch] $file"
        if ! patch --no-backup-if-mismatch "$dest_file" < "$patch_file"; then
            echo "  [WARN] Patch failed for $file — check .rej file for manual resolution."
            touch "$patch_fail_marker"
        fi
    done

    if [ -f "$patch_fail_marker" ]; then
        echo ""
        echo "WARNING: Some patches failed to apply cleanly. Check for .rej files in $destination_dir."
    fi
fi

if [ -n "$added_files" ]; then
    echo ""
    echo "==> Copying added files to destination ..."
    echo "$added_files" | while IFS= read -r file; do
        echo "  [copy] $file"
        mkdir -p "$destination_dir/$(dirname "$file")"
        cp ".build/35-new/$file" "$destination_dir/$file"
    done
fi

if [ -n "$deleted_files" ]; then
    echo ""
    echo "==> Removing deleted files from destination ..."
    echo "$deleted_files" | while IFS= read -r file; do
        dest_file="$destination_dir/$file"
        if [ -f "$dest_file" ]; then
            echo "  [delete] $file"
            rm "$dest_file"
        else
            echo "  [skip] $file (already absent from destination)"
        fi
    done
fi

echo ""
echo "==> Running PHP 7.1 compatibility checks on changed files ..."

check_files=""
if [ -n "$all_changed_files" ]; then
    while IFS= read -r file; do
        dest_file="$destination_dir/$file"
        if [ -f "$dest_file" ]; then
            check_files="$check_files $dest_file"
        fi
    done <<< "$all_changed_files"
fi

if [ -n "$check_files" ]; then
    docker run --rm \
        -v "$(cd "$destination_dir" && pwd):/app" \
        -w /app \
        php:7.1-cli \
        bash -c "
            set -e
            echo '==> Running PHP syntax check on changed files...'
            for f in $check_files; do
                rel=\${f#$destination_dir/}
                php -l \"\$rel\" > /dev/null
            done
            echo '✓ PHP 7.1 syntax check passed!'
        " || {
            echo "✗ PHP 7.1 compatibility check failed!"
            exit 1
        }

    src_check_files=""
    for file in $check_files; do
        case "$file" in
            */src/*|*/includes/*) src_check_files="$src_check_files $file" ;;
        esac
    done

    if [ -n "$src_check_files" ]; then
        vendor/bin/phpcs --standard=config/version-35-compatibility.xml \
            --runtime-set ignore_warnings_on_exit 1 \
            -s \
            $src_check_files || true
    fi

    src_dest_files=""
    for file in $check_files; do
        case "$file" in
            */src/*) src_dest_files="$src_dest_files $file" ;;
        esac
    done

    if [ -n "$src_dest_files" ]; then
        echo ""
        echo "==> Running phpcbf on changed src/ files ..."
        vendor/bin/phpcbf --standard=config/phpcs.xml $src_dest_files || true
    fi
fi

echo "$current_commit" > "$last_build_file"
echo ""
echo "Updated $last_build_file to $current_commit."
echo "Done."
