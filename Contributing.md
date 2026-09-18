# 🤝 Contributing to BanglaQR Gateway by Oi

Thank you for your interest in contributing to **BanglaQR Gateway by Oi**! Open source thrives when communities work together, and we welcome contributions of all kinds—from bug reports and feature ideas to code enhancements, documentation updates, and translations.

By participating in this project, you help Bangladeshi WooCommerce merchants easily accept digital payments through the national interoperable **BanglaQR** framework without expensive gateway fees or merchant contract barriers.

---

## 📑 Table of Contents

- [Code of Conduct](#-code-of-conduct)
- [How Can You Contribute?](#-how-can-you-contribute)
  - [1. Reporting Bugs](#1-reporting-bugs)
  - [2. Suggesting Enhancements](#2-suggesting-enhancements)
  - [3. Improving Documentation](#3-improving-documentation)
  - [4. Language Translations](#4-language-translations)
  - [5. Code Contributions](#5-code-contributions)
- [Development Setup](#-development-setup)
- [Git Workflow & Branching](#-git-workflow--branching)
- [Coding Standards & Guidelines](#-coding-standards--guidelines)
  - [PHP Guidelines](#php-guidelines)
  - [Security Best Practices](#security-best-practices)
  - [JavaScript & CSS Guidelines](#javascript--css-guidelines)
  - [Internationalization (i18n)](#internationalization-i18n)
- [Pull Request (PR) Checklist](#-pull-request-pr-checklist)
- [License](#-license)
- [Community & Contact](#-community--contact)

---

## 📜 Code of Conduct

We are committed to providing a friendly, inclusive, and welcoming environment for everyone, regardless of experience level, background, or identity.

- Be respectful, courteous, and constructive in all discussions.
- Welcome newcomers and encourage diverse perspectives.
- Focus on what is best for the community and WooCommerce merchants.
- Gracefully accept constructive criticism.

For detailed guidelines and reporting procedures, please review our full [Code of Conduct](Code%20of%20Conduct.md).

---

## 💡 How Can You Contribute?

### 1. Reporting Bugs
Encountered an issue or unexpected behavior?
- Check the [existing Issues](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi/issues) to see if the problem has already been reported.
- If not, submit a new report using our [Bug Report Template](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi/issues/new?template=bug_report.md).
- **Security Notice:** If you believe you have discovered a security vulnerability, please do **NOT** open a public issue. Follow our [Security Policy (SECURITY.md)](SECURITY.md) and email **support@oitech.com.bd**.

### 2. Suggesting Enhancements
Have an idea for a feature or UI improvement?
- Open a new proposal using our [Feature Request Template](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi/issues/new?template=feature_request.md).
- Provide a clear explanation of what problem the feature solves and how merchants/customers will benefit.

### 3. Improving Documentation
Clear documentation is as important as good code:
- Correct typos, clarify ambiguous explanations, or expand usage guides in `README.md`.
- Add developer code snippets or hook examples.

### 4. Language Translations
Help make this gateway accessible to merchants and shoppers across Bangladesh:
- Update or create translation files located in the `languages/` directory.
- All strings must use the text domain: `banglaqr-payment-gateway-by-oi`.

### 5. Code Contributions
Whether fixing a reported bug or adding a proposed feature:
- Look for issues tagged `good first issue` or `help wanted`.
- Comment on the issue to let others know you are working on it.

---

## 🛠️ Development Setup

To test and modify this plugin locally:

1. **Prerequisites:**
   - A local WordPress environment (e.g., [LocalWP](https://localwp.com/), Laragon, XAMPP, or Docker).
   - WordPress 5.6+ (preferably the latest 6.x release).
   - WooCommerce 8.0+ (with HPOS enabled for testing modern compatibility).
   - PHP 7.4, 8.1, 8.2, or 8.3.

2. **Clone the Repository:**
   ```bash
   git clone https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi.git
   ```

3. **Install the Plugin:**
   - Link or copy the cloned repository folder into your local WordPress installation:
     `wp-content/plugins/banglaqr-payment-gateway-by-oi/`
   - Activate the plugin via **WordPress Admin > Plugins**.
   - Navigate to **WooCommerce > Settings > Payments > Bangla QR Payment** to configure options.

---

## 🌿 Git Workflow & Branching

1. **Fork the Repository:** Fork on GitHub to your personal account.
2. **Create a Feature Branch:** Branch off from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b fix/your-bug-fix
   ```
3. **Branch Naming Conventions:**
   - `feature/` for new features (e.g., `feature/blocks-checkout-integration`)
   - `fix/` for bug fixes (e.g., `fix/hpos-meta-display`)
   - `docs/` for documentation updates (e.g., `docs/update-contributing-guide`)
   - `refactor/` for code refactoring without behavior change

---

## 📏 Coding Standards & Guidelines

### PHP Guidelines
- **WordPress Coding Standards (WPCS):** Follow official WordPress PHP conventions (PSR-12 inspired with WordPress naming conventions).
- **PHP Compatibility:** Ensure full backward and forward compatibility for **PHP 7.4 through 8.3+**:
  - Do NOT use undeclared dynamic properties (deprecated in PHP 8.2).
  - Do NOT pass `null` into internal string/math functions like `trim()`, `strlen()`, `round()` (deprecated in PHP 8.1).
  - Always guard access with `!empty()` or `isset()`.
- **WooCommerce HPOS:** Always use WooCommerce CRUD getters and setters (`$order->get_meta()`, `$order->update_meta_data()`) instead of direct WordPress post-meta functions (`get_post_meta()`).

### Security Best Practices
Security is a top priority for financial and payment software:
- **Direct Access Prevention:** Every PHP file must begin with:
  ```php
  defined('ABSPATH') || exit;
  ```
- **Nonce Verification:** Every form submission and AJAX endpoint must verify nonces via `check_admin_referer()` or `wp_verify_nonce()`.
- **Input Sanitization:** Sanitize all incoming user data before processing or database storage:
  - `sanitize_text_field()`
  - `sanitize_key()`
  - `absint()` / `floatval()`
  - `esc_url_raw()`
- **Output Escaping:** Escape everything upon output:
  - `esc_html()` / `esc_html__()`
  - `esc_attr()` / `esc_attr__()`
  - `esc_url()`
  - `wp_kses_post()`

### JavaScript & CSS Guidelines
- **Modern, Accessible UI:** Maintain clean UI design with appropriate ARIA roles, focus states, and keyboard accessibility for modals and forms.
- **Lightweight:** Avoid adding heavy third-party libraries; rely on native JavaScript or WordPress core-bundled libraries (like jQuery).
- **Mobile Responsive:** All checkout popups and previews must look great and function seamlessly on mobile devices (320px screens and up).

### Internationalization (i18n)
- Every user-facing text string must be localized:
  ```php
  __('String to translate', 'banglaqr-payment-gateway-by-oi');
  esc_html__('String to translate', 'banglaqr-payment-gateway-by-oi');
  _e('String to translate', 'banglaqr-payment-gateway-by-oi');
  ```
- Never hardcode user-facing strings in HTML or JavaScript without localizing them via `wp_localize_script()`.

---

## 📋 Pull Request (PR) Checklist

Before submitting your PR, please verify the following:

- [ ] My code follows the WordPress PHP and JavaScript coding standards.
- [ ] I have thoroughly tested my changes on both desktop and mobile screens.
- [ ] I have tested with WooCommerce HPOS (High-Performance Order Storage) enabled.
- [ ] No PHP notices, warnings, or deprecation notices appear in `debug.log`.
- [ ] All new and modified strings include the correct text domain (`banglaqr-payment-gateway-by-oi`).
- [ ] My branch is up to date with the upstream `main` branch.
- [ ] The PR description clearly explains **what** was changed and **why**.

---

## 📄 License

By contributing to **BanglaQR Gateway by Oi**, you agree that your contributions will be licensed under the [GPLv3 or later License](LICENSE).

---

## 💬 Community & Contact

- **Organization:** [Oi](https://oitech.com.bd/)
- **Support & Inquiries:** [support@oitech.com.bd](mailto:support@oitech.com.bd)
- **GitHub Discussions & Issues:** [GitHub Repository](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi)

Thank you for helping empower Bangladeshi e-commerce! 🇧🇩🚀
