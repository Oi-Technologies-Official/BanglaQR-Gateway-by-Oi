# 💳 BanglaQR Gateway by Oi

<p align="center">
  <img src="assets/banner-1544x500.png" alt="BanglaQR Gateway by Oi for WooCommerce - Bangladesh QR Payment Plugin" width="100%">
</p>

<p align="center">
  <strong>The Ultimate BanglaQR, MFS & Bank QR Gateway for WooCommerce</strong><br>
  <em>Accept bKash, Nagad, Rocket, Upay, CellFin & Bank QR payments seamlessly with interactive popup, live timer, and receipt verification.</em>
</p>

<p align="center">
  <a href="https://wordpress.org/"><img src="https://img.shields.io/badge/WordPress-5.6%20to%207.1+-21759B?logo=wordpress&logoColor=white" alt="WordPress 5.6 - 7.1+"></a>
  <a href="https://woocommerce.com/"><img src="https://img.shields.io/badge/WooCommerce-HPOS%20Ready-96588A?logo=woocommerce&logoColor=white" alt="WooCommerce HPOS Ready"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%20to%208.3+-777BB4?logo=php&logoColor=white" alt="PHP 7.4 - 8.3+"></a>
  <a href="https://opensource.org/license/mit/"><img src="https://img.shields.io/badge/License-GPL%20v3-green.svg" alt="GPL v3 License"></a>
  <a href="#"><img src="https://img.shields.io/badge/Version-0.2.0-orange.svg" alt="Version 0.2.0"></a>
  <a href="#"><img src="https://img.shields.io/badge/Bangladesh-BanglaQR%20Standard-red.svg" alt="BanglaQR"></a>
</p>

<p align="center">
  <a href="#-why-banglaqr-gateway">Why BanglaQR?</a> •
  <a href="#-key-features">Features</a> •
  <a href="#-how-it-works">How It Works</a> •
  <a href="#-supported-apps--banks">Supported Apps</a> •
  <a href="#-installation--setup">Installation</a> •
  <a href="#-admin-configuration-guide">Configuration</a> •
  <a href="#-screenshots">Screenshots</a> •
  <a href="#-developer-hooks--meta">Developers</a> •
  <a href="#-frequently-asked-questions">FAQ</a> •
  <a href="#-security">Security</a>
</p>

---

## 📖 Why BanglaQR Gateway?

Taking digital payments in Bangladesh often involves expensive setup charges, complicated contracts, merchant account approval delays, and high gateway commissions.

**BanglaQR Gateway by Oi** brings the national interoperable **BanglaQR** framework (introduced by Bangladesh Bank) directly to your WooCommerce store. It bridges the gap between merchant simplicity and customer convenience:

- 💸 **Zero Gateway Fees:** No expensive monthly gateway subscriptions or third-party merchant contracts required.
- 📱 **Universal Compatibility:** Works with any bank app or Mobile Financial Service (MFS) in Bangladesh.
- ⚡ **Seamless Checkout Experience:** Customers scan, pay, and attach proof without navigating away to confusing third-party gateways.
- 🛡️ **Total Control:** Review customer receipts and Transaction IDs directly inside the WooCommerce order dashboard before fulfilling orders.

---

## 🔄 How It Works

```mermaid
sequenceDiagram
    autonumber
    actor Customer as 🛒 Customer
    participant Checkout as 🌐 WooCommerce Checkout
    participant Modal as 🪟 Interactive Popup
    participant MFS as 📱 Banking / MFS App
    actor Merchant as 👨‍💼 Merchant Admin

    Customer->>Checkout: Selects "Bangla QR Payment" & clicks Place Order
    Checkout->>Modal: Opens scan-to-pay popup with QR, order total & 15m timer
    Customer->>MFS: Scans QR code using bKash/Nagad/Bank App & completes payment
    Customer->>Modal: Inputs Transaction ID (TrxID) and/or uploads screenshot slip
    Modal->>Checkout: Submits verified order (Status: On-Hold)
    Checkout->>Merchant: Order arrives in dashboard with previewable receipt & TrxID
    Merchant->>Merchant: Verifies payment & marks order as Processing/Completed
```

---

## ✨ Key Features

| Feature | Description |
|---|---|
| 🇧🇩 **Unified BanglaQR Standard** | Fully compatible with Bangladesh Bank's interoperable QR system — one QR code works across all participating banks and MFS providers. |
| 🪟 **Interactive Scan-to-Pay Modal** | Clean popup modal that displays active QR code, calculated payable total, and a live 15-minute countdown. |
| ⏱️ **1-Click Session Renewal** | If the 15-minute timer expires, customers can extend it with a single click without losing checkout input or reloading. |
| 💰 **Dynamic Percentage Fees** | Set optional gateway processing or cash-out charges (e.g. 1.85% for bKash, 0% for bank transfers) automatically added to checkout. |
| 📸 **Instant Slip & Receipt Upload** | Customers can drag-and-drop or select payment screenshots directly inside the checkout popup. |
| 🗜️ **Client & Server Image Compression** | High-resolution mobile screenshots are automatically resized (max 1200px) and compressed to save hosting storage and bandwidth. |
| 📋 **Manual Mobile Banking Fallback** | Option to show direct personal or merchant numbers (bKash, Nagad, Rocket, Upay, CellFin) with one-click copy buttons. |
| ⚙️ **Customizable Validation Rules** | Configure Transaction ID and Receipt Screenshot fields as **Mandatory**, **Optional**, or **Hidden**. |
| 🔠 **Smart TrxID Formatting** | Automatically normalizes and formats Transaction IDs to clean uppercase alphanumeric characters to eliminate customer typos. |
| 🎨 **Theme Color Customizer** | Choose an accent color that perfectly matches your store's branding and theme typography. |
| 🔄 **Drag-and-Drop QR Manager** | Add multiple QR accounts, prioritize the active QR code, and reorder accounts with an intuitive admin interface. |
| 🚀 **Full HPOS Compatibility** | Fully tested and compatible with WooCommerce High-Performance Order Storage (HPOS) and legacy post-based storage. |
| 🧹 **Automated Storage Cleanup** | Built-in daily WP-Cron purges unattached temporary receipts older than 24 hours to prevent server disk bloat. |

---

## 📱 Supported Apps & Banks

Customers can pay using any app compatible with BanglaQR or direct MFS transfer:

| Mobile Financial Services (MFS) | Banks & Digital Wallets |
|---|---|
| • **bKash** (App & Send Money / Merchant) | • **CellFin** (Islami Bank Bangladesh) |
| • **Nagad** (App & Send Money / Merchant) | • **Citytouch** (City Bank) |
| • **Rocket** (Dutch-Bangla Bank) | • **Astha** (BRAC Bank) |
| • **Upay** (UCB) | • **MTB Smart Banking** (Mutual Trust Bank) |
| • **Tap** / Trust Axiata Pay | • **EBL Skybanking** (Eastern Bank) |
| • **MCash**, **SureCash**, **OK Wallet** | • **Any other bank app supporting BanglaQR** |

---

## ⚙️ Installation & Setup

### Requirements:
- **WordPress:** 5.6 or higher (Tested up to 6.7)
- **WooCommerce:** 5.0 or higher
- **PHP:** 7.4 to 8.3+
- **PHP Extensions:** `gd` or `imagick` (for image compression and slip processing)

### Quick Setup:
1. **Download & Upload:**
   - Download the plugin `.zip` from the [Releases](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi/releases) section.
   - In your WordPress Admin, navigate to **Plugins → Add New → Upload Plugin**.
   - Select the `.zip` file and click **Install Now**.
2. **Activate:**
   - Click **Activate Plugin** once the installation is complete.
3. **Configure Settings:**
   - Go to **WooCommerce → Settings → Payments**.
   - Click **Manage** next to **Bangla QR Payment**.
   - Enable the gateway, upload your QR code, and save! 🎉

---

## 🛠️ Admin Configuration Guide

Navigate to **WooCommerce → Settings → Payments → Bangla QR Payment** to configure the following options:

### 1. General Gateway Settings
- **Enable/Disable:** Turn the BanglaQR gateway on or off.
- **Payment Title:** The title displayed to customers on the checkout page (Default: `Bangla QR Payment`).
- **Gateway Logo:** Provide a custom logo image URL or use the built-in default logo.
- **Payment Description:** Instructions shown when the customer selects this payment option.
- **Theme Accent Color:** Pick a custom HEX color for modal buttons, highlights, and borders.
- **Default Order Status:** Choose initial status for newly placed orders (`On-Hold`, `Processing`, `Completed`, or `Pending Payment`).

### 2. QR Accounts Manager
- **Add / Remove Accounts:** Add multiple QR codes for different banks or MFS providers.
- **Active Account:** Select which QR code will be presented to customers on checkout.
- **Payment Charge (%):** Specify a fee percentage (e.g., `1.85` for bKash cashout fee, `0` for no charge).
- **Drag-and-Drop:** Easily drag and drop QR accounts to organize your accounts list.

### 3. Receipt & Transaction ID Rules
- **Receipt Screenshot Rule:**
  - `Mandatory`: Customer must upload a screenshot before the order is placed.
  - `Optional`: Customer can upload a screenshot or skip.
  - `Hidden`: Upload field is completely hidden.
- **Transaction ID Rule:**
  - `Mandatory`: Customer must enter a TrxID.
  - `Optional`: TrxID is optional.
  - `Hidden`: TrxID input field is hidden.

### 4. Manual Mobile Banking Numbers
- **Enable Manual Payment:** Check to display manual account numbers below the QR code.
- **Account Numbers:** Enter your numbers for **bKash**, **Nagad**, **Rocket**, **Upay**, and **CellFin**. Customers get a convenient 1-click copy button for each number.

---

## 🖼️ Screenshots

| # | Screen | Description |
|---|---|---|
| 1 | ![QR Accounts Manager](assets/screenshot-1.png) | **QR Accounts Manager** — Add, sort, and activate multiple payment QR codes. |
| 2 | ![Gateway Settings](assets/screenshot-2.png) | **Gateway Settings** — Set fees, custom titles, manual numbers, and upload rules. |
| 3 | ![Checkout Popup](assets/screenshot-3.png) | **Checkout Popup** — Responsive scan-to-pay modal with QR, timer, and slip upload. |
| 4 | ![Payment Method](assets/screenshot-4.png) | **Checkout Selection** — Seamlessly integrates with default WooCommerce checkout. |
| 5 | ![Receipt Preview](assets/screenshot-5.png) | **Admin Order Details** — Review customer Transaction ID and inspect full payment receipt. |

---

## 👨‍💻 Developer Hooks & Meta

For developers looking to customize or integrate the plugin with custom themes and ERPs:

### Filters
```php
// Make the payment processing fee taxable or non-taxable (default: false)
add_filter('oi_banglaqr_fee_is_taxable', function($is_taxable) {
    return true; // Set to true if fees should include store taxes
});

// Customize the gateway icon HTML
add_filter('woocommerce_gateway_icon', function($icon, $gateway_id) {
    if ($gateway_id === 'oi_banglaqr') {
        // Custom icon logic
    }
    return $icon;
}, 10, 2);
```

### Order Metadata Keys
When an order is created via BanglaQR, the following metadata is saved to the `WC_Order`:
- `_oi_banglaqr_selected_qr`: The name of the QR account selected for the payment.
- `_oi_banglaqr_transaction_id`: The transaction ID entered by the customer.
- `_oi_banglaqr_receipt_id`: The WordPress media attachment ID (or URL) of the uploaded receipt.

You can retrieve these values in custom code:
```php
$order = wc_get_order($order_id);
$trx_id     = $order->get_meta('_oi_banglaqr_transaction_id');
$receipt_id = $order->get_meta('_oi_banglaqr_receipt_id');
$qr_name    = $order->get_meta('_oi_banglaqr_selected_qr');
```

---

## 🔐 Security & Data Integrity

We prioritize the security of both merchants and customers:

- 🛡️ **Session-Locked Uploads:** Uploaded payment slips are strictly bound to the customer's active WooCommerce session token, preventing Insecure Direct Object Reference (IDOR) exploits.
- 🛡️ **Strict File MIME Verification:** Server-side validation inspects real file headers and MIME types (`image/jpeg`, `image/png`, `image/webp`). Executable scripts or forged extensions are immediately blocked.
- 🛡️ **Automated Orphan File Pruning:** A scheduled daily WP-Cron cleans up unassociated temporary uploads older than 24 hours, preventing storage abuse.
- 🛡️ **Input Sanitization & Escaping:** All Transaction IDs, field values, and user inputs are strictly sanitized and escaped against SQL injection and XSS.
- 🛡️ **HPOS & WooCommerce Standards:** Compliant with WooCommerce High-Performance Order Storage (HPOS) and official WordPress Plugin Coding Guidelines.

---

## ❓ Frequently Asked Questions

<details>
<summary><strong>🔹 Do I need an official BanglaQR merchant account to use this plugin?</strong></summary>
<br>
No, an official merchant account is not required! You can upload an official bank merchant BanglaQR (City Bank, BRAC Bank, MTB, EBL, etc.), or use personal/agent QR codes from bKash, Nagad, Rocket, or CellFin. You can also provide manual phone numbers.
</details>

<details>
<summary><strong>🔹 Can I add payment processing or cash-out charges to the customer's total?</strong></summary>
<br>
Yes! You can specify a percentage fee (e.g. 1.85% for bKash, or 0% for bank transfers) for each QR account. When the customer selects that QR, the fee is automatically calculated and added to the order total.
</details>

<details>
<summary><strong>🔹 Where do I view the customer's payment slip and Transaction ID?</strong></summary>
<br>
When an order is placed, open the order in <strong>WooCommerce → Orders</strong>. A dedicated BanglaQR receipt card will show the customer's Transaction ID and a preview of their uploaded payment screenshot, with a one-click link to view the full resolution image.
</details>

<details>
<summary><strong>🔹 What happens if the customer's 15-minute session timer expires?</strong></summary>
<br>
The modal will display a friendly message with a <strong>"Renew Session (15m)"</strong> button. The customer can reset the timer with one click without losing entered information or reloading the page.
</details>

<details>
<summary><strong>🔹 Does this work for guest checkouts?</strong></summary>
<br>
Yes. Guest checkouts are fully supported through secure, temporary session tokens linked directly to the guest's checkout session.
</details>

<details>
<summary><strong>🔹 Is this plugin compatible with WooCommerce High-Performance Order Storage (HPOS)?</strong></summary>
<br>
Yes, the plugin is 100% compatible with HPOS and works flawlessly on both modern HPOS tables and classic post-meta order storage.
</details>

---

## 📝 Changelog

### 🚀 Version 0.2.0
- 🔒 **Security:** Fixed IDOR vulnerability by tying uploaded receipts to WooCommerce session tokens.
- 🔒 **Security:** Added strict server-side MIME type verification for all uploaded payment slips.
- 🔒 **Security:** Automated daily cleanup cron to purge orphaned temporary upload files.
- 🐛 **Fix:** Resolved issue where percentage fees could calculate as `0.00` on checkout.
- 🐛 **Fix:** Resolved infinite modal loop when checkout rules were set to hidden.
- ✨ **UI/UX:** Added one-click session renewal when the 15-minute timer finishes.
- ✨ **UI/UX:** Modernized checkout confirmation cards with clean badges and responsive typography.
- ✨ **UI/UX:** Added mobile bottom-sheet styling and touch target optimizations.

---

## 🤝 Contributing

Contributions are warmly welcomed! If you've found a bug, want to suggest an improvement, or want to contribute code:

1. **Fork** this repository.
2. **Create a branch** for your feature or fix:
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit your changes**:
   ```bash
   git commit -m "feat: add support for custom modal header"
   ```
4. **Push to your fork**:
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open a Pull Request** describing your changes.

---

## 📄 License

This project is licensed under the **MIT License** — free to use, modify, and distribute for both personal and commercial projects. See the [LICENSE](LICENSE) file for details.

---

## 🏢 About Oi Technologies

**Oi Technologies** builds high quality, accessible open-source tools that empower merchants, developers, and digital businesses across Bangladesh.

- 🌐 **Website:** [oitech.com.bd](https://oitech.com.bd)
- 📧 **Support:** [support@oitech.com.bd](mailto:support@oitech.com.bd)
- 🔓 **More Open Source Projects:** [Oi Open Source Projects](https://oitech.com.bd/open-source/)
- 💬 **Contact Us:** [oitech.com.bd/helpline/contact-us](https://oitech.com.bd/helpline/contact-us/)

---

<p align="center">
  <strong>Crafted with ❤️ for the Bangladeshi eCommerce Community 🇧🇩</strong><br>
  <em>Simplifying digital payments, one store at a time.</em>
</p>
