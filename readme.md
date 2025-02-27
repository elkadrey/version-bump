# Version Bump - Composer Plugin

A Composer plugin that allows you to manage versioning similar to `npm version`. It supports `patch`, `minor`, `major`, `prepatch`, `preminor`, `premajor`, and `prerelease` versioning while integrating with Git for tagging and commits.

## 🚀 Features
- Easily update version numbers in `composer.json`
- Automatically commit and tag new versions in Git
- Supports **pre-release versions** (`beta`)
- Compatible with **semantic versioning (SemVer)**

---

## 📥 Installation
Install the plugin globally or in a specific project:

```sh
composer require elkadrey/version-bump --dev
```

If using locally, require it as a **path repository**:
```sh
composer config repositories.version-bump '{"type": "path", "url": "../path-to-plugin"}'
composer require elkadrey/version-bump:dev-main
```

---

## 🔧 Usage
Run the following command to manage versioning:

```sh
composer version <command>
```

Set a specific version manually:
```sh
composer version <version>
```
This will set the version in `composer.json` to exactly what `<version>` is.

---

## 🎯 Available Commands

| Command      | Description |
|-------------|-------------|
| `prerelease`| Only increment beta version (`beta.1` → `beta.2`) |
| `patch`     | Bump patch version (1.0.0 → 1.0.1) |
| `minor`     | Bump minor version (1.0.0 → 1.1.0) |
| `major`     | Bump major version (1.0.0 → 2.0.0) |
| `prepatch`  | Bump pre-release patch (1.0.0 → 1.0.1-beta.1) |
| `preminor`  | Bump pre-release minor (1.0.0 → 1.1.0-beta.1) |
| `premajor`  | Bump pre-release major (1.0.0 → 2.0.0-beta.1) |
| `<version>` | Set version to `<version>` |
| `--show`    | Show the current version |
| `--no-git`  | Skip Git commit and tagging |

---

## 📌 Examples

### 🔹 Standard Version Bumps
```sh
composer version patch      # 1.0.0 → 1.0.1
composer version minor      # 1.0.1 → 1.1.0
composer version major      # 1.1.0 → 2.0.0
```

### 🔹 Pre-release Versioning
```sh
composer version prepatch   # 1.0.0 → 1.0.1-beta.1
composer version preminor   # 1.0.0 → 1.1.0-beta.1
composer version premajor   # 1.0.0 → 2.0.0-beta.1
```

### 🔹 Increment Only Beta Version
```sh
composer version prerelease  # 1.0.0-beta.1 → 1.0.0-beta.2
```

### 🔹 Set a Specific Version
```sh
composer version 2.1.5  # Sets the version to 2.1.5 in composer.json
```

### 🔹 Skipping Git Operations
```sh
composer version patch --no-git  # Updates version without Git commit/tag
```

---

## 🛠 Development
Clone the repository and install dependencies:
```sh
git clone https://github.com/elkadrey/version-bump.git
cd version-bump
composer install
```
Test locally using a path repository in another project:
```sh
composer config repositories.local '{"type": "path", "url": "../version-bump"}'
composer require elkadrey/version-bump:dev-main
```

---

## 📜 License
This project is licensed under the **MIT License**.

