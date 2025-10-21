# 📦 Version Management Guide

This document explains how to manage versions and changelog for the Simple AI Page Generator plugin.

## 🎯 Overview

The plugin uses **Semantic Versioning** (SemVer) and provides automated scripts to manage versions and changelog entries.

### Semantic Versioning Format

```
MAJOR.MINOR.PATCH
```

- **MAJOR** (X.0.0): Breaking changes, incompatible API changes
- **MINOR** (0.X.0): New features, backward-compatible
- **PATCH** (0.0.X): Bug fixes, backward-compatible

## 🛠️ Available Scripts

All scripts are located in the `bin/` directory.

### 1. Add Changelog Entry

Add a new entry to the CHANGELOG.md under the [Unreleased] section.

```bash
php bin/changelog.php <type> "<message>"
```

**Types:**
- `added` ✨ - New features
- `changed` 🔄 - Changes in existing functionality
- `deprecated` ⚠️ - Soon-to-be removed features
- `removed` 🗑️ - Removed features
- `fixed` 🐛 - Bug fixes
- `security` 🔒 - Security fixes

**Examples:**

```bash
# Add a new feature
php bin/changelog.php added "Support for GPT-4 Turbo model"

# Fix a bug
php bin/changelog.php fixed "Resolved API timeout issue"

# Security fix
php bin/changelog.php security "Fixed XSS vulnerability in admin panel"

# Change existing feature
php bin/changelog.php changed "Improved cache performance"
```

### 2. Update Version

Update the plugin version across all files and move [Unreleased] changes to a new version.

```bash
php bin/update-version.php <version> "<summary>"
```

**Example:**

```bash
php bin/update-version.php 2.0.1 "Bug fixes and performance improvements"
```

**What it does:**
- ✅ Updates version in `simple-ai-page-generator.php`
- ✅ Updates version in `includes/class-config.php`
- ✅ Updates version in `README.md`
- ✅ Updates version in `readme.txt`
- ✅ Creates new changelog entry with date

### 3. Complete Release

Automated release workflow that handles everything.

```bash
php bin/release.php <version> <type>
```

**Types:**
- `major` - Breaking changes (1.0.0 → 2.0.0)
- `minor` - New features (1.0.0 → 1.1.0)
- `patch` - Bug fixes (1.0.0 → 1.0.1)

**Example:**

```bash
php bin/release.php 2.1.0 minor
```

**What it does:**
1. ✅ Checks git status
2. ✅ Updates CHANGELOG.md (moves [Unreleased] to new version)
3. ✅ Updates version in all files
4. ✅ Creates git commit
5. ✅ Creates git tag
6. ✅ Provides push instructions

## 📝 Workflow Examples

### Scenario 1: Adding Features During Development

```bash
# Add features to changelog as you develop
php bin/changelog.php added "New API client for Mistral AI"
php bin/changelog.php added "Bulk content generation feature"
php bin/changelog.php changed "Improved error messages"

# When ready to release
php bin/release.php 2.1.0 minor
git push && git push --tags
```

### Scenario 2: Quick Bug Fix

```bash
# Add bug fix to changelog
php bin/changelog.php fixed "Corrected API key validation"

# Release patch version
php bin/release.php 2.0.1 patch
git push && git push --tags
```

### Scenario 3: Security Update

```bash
# Add security fix
php bin/changelog.php security "Fixed SQL injection vulnerability"

# Release immediately
php bin/release.php 2.0.2 patch
git push && git push --tags
```

### Scenario 4: Major Version with Breaking Changes

```bash
# Document breaking changes
php bin/changelog.php changed "Refactored API client interface (BREAKING)"
php bin/changelog.php removed "Deprecated legacy API support"
php bin/changelog.php added "New configuration system"

# Release major version
php bin/release.php 3.0.0 major
git push && git push --tags
```

## 📋 CHANGELOG.md Structure

The CHANGELOG.md follows the [Keep a Changelog](https://keepachangelog.com/) format:

```markdown
# Changelog

## [Unreleased]

### Added ✨
- New feature description

### Fixed 🐛
- Bug fix description

## [2.0.1] - 2025-01-21

### Fixed 🐛
- Bug fix description

## [2.0.0] - 2025-01-21

### Added ✨
- Feature description
```

## 🔄 Version Update Checklist

When releasing a new version, ensure:

- [ ] All changes are documented in CHANGELOG.md
- [ ] Version follows semantic versioning
- [ ] All tests pass
- [ ] Documentation is updated
- [ ] README.md reflects new features
- [ ] Breaking changes are clearly documented
- [ ] Migration guide provided (if needed)

## 📂 Files Updated by Scripts

The version management scripts automatically update:

1. **simple-ai-page-generator.php**
   - Plugin header version
   - `SAPG_VERSION` constant

2. **includes/class-config.php**
   - `VERSION` constant

3. **README.md**
   - Version badge

4. **readme.txt**
   - Stable tag

5. **CHANGELOG.md**
   - New version entry with date

## 🎯 Best Practices

### 1. Commit Messages

Use conventional commits format:

```bash
feat: add support for new AI model
fix: resolve API timeout issue
docs: update installation guide
chore: bump version to 2.1.0
security: patch XSS vulnerability
```

### 2. Changelog Entries

Be clear and descriptive:

```bash
# Good ✅
php bin/changelog.php fixed "Resolved timeout issue when generating long content (>2000 words)"

# Bad ❌
php bin/changelog.php fixed "Fixed bug"
```

### 3. Version Bumping

Follow semantic versioning strictly:

- **Patch** (2.0.X): Bug fixes only
- **Minor** (2.X.0): New features, backward-compatible
- **Major** (X.0.0): Breaking changes

### 4. Release Timing

- **Patch releases**: As needed for critical bugs
- **Minor releases**: Every 2-4 weeks with new features
- **Major releases**: When breaking changes are necessary

## 🚀 GitHub Release

After pushing tags, create a GitHub release:

1. Go to GitHub repository
2. Click "Releases" → "Create a new release"
3. Select the tag (e.g., v2.1.0)
4. Copy changelog content for this version
5. Add release notes
6. Publish release

## 🔧 Troubleshooting

### Script Permission Issues (Linux/Mac)

```bash
chmod +x bin/*.php
```

### Git Tag Already Exists

```bash
# Delete local tag
git tag -d v2.0.1

# Delete remote tag
git push origin :refs/tags/v2.0.1

# Create new tag
php bin/release.php 2.0.1 patch
```

### Undo Last Release

```bash
# Undo commit (keep changes)
git reset --soft HEAD~1

# Delete tag
git tag -d v2.0.1
```

## 📞 Support

For questions about version management:
- 📧 Email: support@infinityweb.tn
- 💻 GitHub: [Issues](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)

---

**Last Updated**: 2025-01-21  
**Version**: 2.0.0
