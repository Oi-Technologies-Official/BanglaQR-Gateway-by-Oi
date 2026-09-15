# 💳 BanglaQR Payment Gateway for WooCommerce

<p align="center">
  <img src="assets/banner-1544x500.png" alt="BanglaQR Payment Gateway for WooCommerce - Bangladesh QR Payment Plugin" width="100%">
</p>

<p align="center">
  <strong>Accept BanglaQR, bKash, Nagad, Rocket, Upay, CellFin & Bank QR Payments on Your WooCommerce Store</strong>
</p>

<p align="center">
  <a href="https://wordpress.org/"><img src="https://img.shields.io/badge/WordPress-5.6%2B-blue?logo=wordpress&logoColor=white" alt="WordPress 5.6+"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+"></a>
  <a href="https://woocommerce.com/"><img src="https://img.shields.io/badge/WooCommerce-Compatible-96588A?logo=woocommerce&logoColor=white" alt="WooCommerce"></a>
  <a href="https://opensource.org/license/mit/"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT License"></a>
  <a href="#"><img src="https://img.shields.io/badge/Version-0.2.0-orange.svg" alt="Version 0.2.0"></a>
  <a href="#"><img src="https://img.shields.io/badge/Bangladesh-BanglaQR-red.svg" alt="BanglaQR"></a>
</p>

<p align="center">
  <a href="#-features">Features</a> •
  <a href="#-installation">Installation</a> •
  <a href="#-screenshots">Screenshots</a> •
  <a href="#-changelog">Changelog</a> •
  <a href="#-faq">FAQ</a> •
  <a href="#-license">License</a>
</p>

---

## 📖 Overview

**BanglaQR Payment Gateway** is a powerful WooCommerce plugin built for merchants in **Bangladesh**. It supports the unified **BanglaQR** national payment standard launched by **Bangladesh Bank**, allowing customers to pay using **any bank app** or **Mobile Financial Service (MFS)** — including **bKash, Nagad, Rocket, Upay, CellFin, City Touch**, and traditional bank transfers.

Customers simply **scan a QR code**, complete the payment, and **upload their payment receipt** — all without leaving your checkout page.

> 🎯 Perfect for: eCommerce stores, online shops, small businesses, freelancers, and any Bangladeshi merchant using WooCommerce.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🇧🇩 **BanglaQR Integration** | Fully aligned with Bangladesh Bank's national QR payment standard. |
| 📱 **MFS & Bank Compatibility** | Works with bKash, Nagad, Rocket, Upay, CellFin, City Touch & bank apps. |
| 🪟 **Scan-to-Pay Popup Modal** | Responsive checkout modal showing payable amount, active QR & instructions. |
| 💰 **Per-QR Percentage Charges** | Define custom bank fees (e.g., 1.5% for cards, 0.7% for bKash) auto-added to totals. |
| 📸 **Proof of Payment Upload** | Customers upload payment screenshots directly in the checkout popup. |
| 🖼️ **Auto Image Optimization** | Receipts auto-rotate, resize to 1200px & compress to 70% quality. |
| 📋 **Manual Payment Accounts** | Fallback bKash/Nagad/Rocket/Upay/CellFin numbers with brand-colored copy buttons. |
| ⚙️ **Smart Checkout Rules** | Configure Receipt Upload & Transaction ID as required, optional, or hidden. |
| ✅ **Transaction ID Validation** | Auto-sanitizes TrxID to uppercase alphanumeric format. |
| 🎨 **Enhanced UI/UX** | Zoomable previews, theme-inherited fonts, countdown timer & success animation. |
| 🔄 **Drag-and-Drop QR Manager** | Sort & prioritize multiple QR accounts with a sortable panel. |
| ⚡ **Settings Shortcut** | Quick "Settings" link on the WordPress Plugins page. |
| 👁️ **Attachment Preview** | Preview uploaded slips inside the order details page. |

---

## ⚙️ Installation

### Requirements
- WordPress **5.6** or higher
- PHP **7.4** or higher
- WooCommerce installed & activated

### Steps

1. **Download** the plugin and upload the `banglaqr-payment-gateway-banglaqr` folder to `/wp-content/plugins/`.
2. **Activate** the plugin from the **Plugins** menu in WordPress.
3. Navigate to **WooCommerce → Settings → Payments → Bangla QR Payment**.
4. Configure your **QR accounts**, **charges**, and **payment options**.
5. Save changes — you're ready to accept BanglaQR payments! 🎉

---

## 🖼️ Screenshots

| # | Preview | Description |
|---|---------|-------------|
| 1 | ![QR Accounts Manager](assets/screenshot-1.png) | **QR Accounts Manager** — Add & sort multiple QR accounts. |
| 2 | ![Gateway Settings](assets/screenshot-2.png) | **Gateway Settings** — Configure fees, MFS numbers & rules. |
| 3 | ![Checkout Popup](assets/screenshot-3.png) | **Checkout Payment Popup** — Scan & upload receipt. |
| 4 | ![Payment Method](assets/screenshot-4.png) | **Checkout Page** — Payment method selection. |
| 5 | ![Receipt Preview](assets/screenshot-5.png) | **Customer Receipt** — Uploaded screenshot preview. |

---

## 📝 Changelog

### 🚀 Version 0.2.0
- 🔒 **Security:** Fixed IDOR vulnerability by tying uploaded receipts to WooCommerce user sessions.
- 🔒 **Security:** Added strict server-side MIME type validation for base64 image uploads.
- 🔒 **Security:** Added daily automated cron job to clean up orphaned receipt uploads.
- 🐛 **Fix:** Corrected payment charge incorrectly calculating as `0.00` on checkout.
- 🐛 **Fix:** Prevented BanglaQR fee from showing on cart page or other payment methods.
- ✨ **Enhancement:** Added toggle button with slide animation for manual MFS options.
- ✨ **Enhancement:** Updated MFS account names with native brand colors & centered layout.

---

## ❓ Frequently Asked Questions

<details>
<summary><strong>🔹 Do I need a BanglaQR code?</strong></summary>
<br>
Yes. You can generate and use your official <strong>BanglaQR code</strong> (or any bank/MFS QR code) inside the QR accounts manager.
</details>

<details>
<summary><strong>🔹 Can I charge extra fees for payment methods?</strong></summary>
<br>
Yes. Define a customized percentage charge (e.g., <code>1.85%</code> for bKash, <code>0%</code> for bank transfers) inside each QR account. The plugin dynamically calculates charges based on cart total and updates in real time.
</details>

<details>
<summary><strong>🔹 Where do I verify customer-uploaded screenshots?</strong></summary>
<br>
Shop admins can view and download uploaded receipt proofs directly under the <strong>"Order Details"</strong> panel in <strong>WooCommerce → Orders</strong>.
</details>

<details>
<summary><strong>🔹 Does it support guest checkout?</strong></summary>
<br>
Yes, guest customers can checkout and upload their payment screenshots securely.
</details>

---

## 🔐 Security Highlights

- ✅ Server-side **MIME type validation** on all uploaded images
- ✅ **Session-tied receipt uploads** to prevent IDOR attacks
- ✅ **Daily cron cleanup** of orphaned receipt files
- ✅ **Sanitized transaction IDs** enforced server-side
- ✅ Built following **WordPress coding standards**

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!  
Feel free to check the [issues page](https://github.com/) or submit a pull request.

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m "Add amazing feature"`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** — free to use, modify, and distribute.

See the [LICENSE](https://opensource.org/license/mit/) file for full details.

---

## 🏢 About Oi Technologies

**Oi Technologies** builds open-source tools for the Bangladeshi web community.

- 🌐 **Website:** [https://oitech.com.bd](https://oitech.com.bd)
- 📧 **Email:** [support@oitech.com.bd](mailto:support@oitech.com.bd)
- 🔓 **Open Source:** [https://oitech.com.bd/open-source/](https://oitech.com.bd/open-source/)
- 📞 **Contact:** [https://oitech.com.bd/helpline/contact-us/](https://oitech.com.bd/helpline/contact-us/)

---

## ⭐ Support This Project

If **BanglaQR Payment Gateway** helped your business, please:

- ⭐ **Star this repository**
- 🐛 **Report bugs** and suggest features
- 📢 **Share** it with fellow Bangladeshi merchants

---

<p align="center">
  <strong>Made with ❤️ in Bangladesh 🇧🇩</strong><br>
  <em>Empowering Bangladeshi merchants with modern QR payments.</em>
</p>
