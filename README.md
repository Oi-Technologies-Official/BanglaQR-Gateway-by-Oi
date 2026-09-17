# 💳 BanglaQR Payment Gateway by Oi

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
  <a href="#-why-banglaqr-payment-gateway">Why BanglaQR?</a> •
  <a href="#-what-you-get">Features</a> •
  <a href="#-quick-setup-guide">Installation</a> •
  <a href="#-screenshots">Screenshots</a> •
  <a href="#-frequently-asked-questions">FAQ</a> •
  <a href="#-built-with-security-in-mind">Security</a> •
  <a href="#-want-to-contribute">Contributing</a>
</p>

---

## 📖 Why BanglaQR Payment Gateway?

Taking payments in Bangladesh shouldn't mean dealing with expensive gateway setup charges, complex contracts, or confusing checkout pages.

**BanglaQR Payment Gateway** brings Bangladesh Bank's unified national **BanglaQR** payment standard directly to your WooCommerce store. Your customers can pay effortlessly using any bank app or Mobile Financial Service (MFS) on their phone — including **bKash, Nagad, Rocket, Upay, CellFin, City Touch, BRAC Bank Astha**, and more.

### How the checkout flow works:
1. **Customer selects BanglaQR** at checkout.
2. **An interactive popup appears** displaying your active QR code, the exact payable total, and a live session timer.
3. **They scan and pay** using their preferred banking or mobile wallet app.
4. **They enter their Transaction ID or upload a screenshot receipt** right inside the popup and confirm.
5. **You verify with confidence:** Review the Transaction ID and preview the customer's uploaded receipt directly inside your WooCommerce order dashboard.

> 💡 **Designed for everyone:** Whether you run an online boutique, tech store, grocery shop, or freelance service, this plugin lets you take QR payments smoothly from day one.

---

## ✨ What You Get

| Feature | Why It Matters |
|---|---|
| 🇧🇩 **Unified BanglaQR Standard** | Fully compliant with Bangladesh Bank's interoperable QR framework — one QR works across participating banks and MFS apps. |
| 📱 **Wide MFS & Bank Support** | Ready for bKash, Nagad, Rocket, Upay, CellFin, and any bank supporting QR payments. |
| 🪟 **Interactive Checkout Modal** | Clean popup modal that shows live order totals, active QR code, and an automatic 15-minute countdown. |
| 💰 **Custom Percentage Fees** | Add optional processing or cash-out charges (e.g. 1.85% for bKash, 0% for bank transfers) automatically to the order total. |
| 📸 **Instant Receipt Uploads** | Customers can easily drop or select a payment screenshot right in the checkout popup. |
| 🖼️ **Auto Image Compression** | Large phone screenshots are automatically resized to 1200px and compressed so your server storage stays light. |
| 📋 **Manual MFS Account Numbers** | Provide fallback personal or merchant numbers with one-click copy buttons for customers who prefer manual transfers. |
| ⚙️ **Flexible Confirmation Rules** | Require a receipt screenshot, a Transaction ID, either one, or make both optional based on your business workflow. |
| ✅ **Smart TrxID Formatting** | Auto-formats Transaction IDs to clean uppercase alphanumeric characters to stop customer typos. |
| 🔄 **Drag-and-Drop QR Manager** | Easily sort and prioritize which QR accounts appear first on your checkout. |
| 👁️ **Dashboard Order Verification** | Preview uploaded screenshots and inspect Transaction IDs directly on the WooCommerce edit order screen. |
| ⏱️ **Zero-Friction Session Renewal** | If the 15-minute session timer expires, customers can renew it with one click without losing their checkout information. |

---

## ⚙️ Quick Setup Guide

Getting your store ready for BanglaQR takes less than 2 minutes:

### What you need:
- WordPress **5.6** or higher
- PHP **7.4** or higher
- An active WooCommerce store

### Step-by-Step:
1. **Install:** Download the plugin folder and place it in your `/wp-content/plugins/` directory (or upload the `.zip` via **Plugins → Add New → Upload Plugin**).
2. **Activate:** Go to **Plugins** in your WordPress dashboard and click **Activate** under **BanglaQR Payment Gateway by Oi**.
3. **Configure:** Open **WooCommerce → Settings → Payments** and click **Manage** next to **Bangla QR Payment**.
4. **Add Your QR:** Upload your merchant or personal QR code, enter your account details, and set any fee percentages you need.
5. **Save Changes:** You're all set! Customers will immediately see BanglaQR at checkout. 🎉

---

## 🖼️ Screenshots

| # | Screen | What You See |
|---|---|---|
| 1 | ![QR Accounts Manager](assets/screenshot-1.png) | **QR Accounts Manager** — Add, edit, and reorder multiple payment QR accounts. |
| 2 | ![Gateway Settings](assets/screenshot-2.png) | **Gateway Settings** — Set fees, custom titles, manual numbers, and upload rules. |
| 3 | ![Checkout Popup](assets/screenshot-3.png) | **Checkout Popup** — Clean, responsive modal with QR code, timer, and upload field. |
| 4 | ![Payment Method](assets/screenshot-4.png) | **Checkout Selection** — Seamlessly integrated with WooCommerce checkout. |
| 5 | ![Receipt Preview](assets/screenshot-5.png) | **Order Details View** — Clear preview of uploaded slips and Transaction IDs. |

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

## ❓ Frequently Asked Questions

<details>
<summary><strong>🔹 Do I need an official BanglaQR merchant account to use this?</strong></summary>
<br>
Not necessarily! While the plugin fully supports official merchant BanglaQR codes issued by banks (such as City Bank, BRAC Bank, Eastern Bank, Mutual Trust Bank, etc.), you can also upload personal or agent QR codes from bKash, Nagad, or Rocket. You can also provide your numbers for manual transfer.
</details>

<details>
<summary><strong>🔹 Can I add payment processing or cash-out charges to the customer's total?</strong></summary>
<br>
Yes! You can set an optional percentage fee (for example, 1.85% for bKash or 0% for bank transfers) for each QR account. When the customer chooses that account, the fee is automatically calculated and added to the order total in real time.
</details>

<details>
<summary><strong>🔹 Where do I check the customer's payment screenshot and Transaction ID?</strong></summary>
<br>
Whenever a new order arrives, open it in <strong>WooCommerce → Orders</strong>. Under the order details, you'll see the customer's Transaction ID and a thumbnail preview of their payment receipt. You can click to view the full screenshot or download it to verify with your statement.
</details>

<details>
<summary><strong>🔹 What happens if a customer takes longer than 15 minutes in the popup?</strong></summary>
<br>
To keep payment sessions fresh, the modal features a 15-minute countdown. If time expires, the customer sees a friendly message with a one-click <strong>"Renew Session (15m)"</strong> button that restarts the timer instantly without losing their form progress or reloading the page.
</details>

<details>
<summary><strong>🔹 Does this work for guest customers who aren't logged in?</strong></summary>
<br>
Yes. Guest checkouts are fully supported. The plugin creates a secure, temporary session token so guest receipts are safely stored and linked to their specific order without risk of data leaks.
</details>

---

## 🔐 Built with Security in Mind

Payment data and file uploads must always be handled with care:

- 🛡️ **Session-Locked Uploads:** Every uploaded receipt is bound to the customer's verified WooCommerce session token, preventing IDOR attacks or receipt swapping between orders.
- 🛡️ **Server-Side File Verification:** Uploads are checked on the server for valid image MIME types and headers (JPEG, PNG, WebP). Malicious file types or executable extensions are rejected immediately.
- 🛡️ **Automated File Cleanup:** Temporary uploads that never turned into completed orders are safely pruned after 24 hours by a background cron job. Completed order receipts are permanently protected.
- 🛡️ **Input Sanitization:** Transaction IDs and account details are sanitized across both client and server to prevent XSS and database injection.
- 🛡️ **WordPress Standards Compliant:** Follows standard WordPress and WooCommerce coding best practices, including full High-Performance Order Storage (HPOS) compatibility.

---

## 🤝 Want to Contribute?

We love community contributions! Whether you spotted a bug, want to suggest an improvement, or have code to share, your help makes this plugin better for every merchant in Bangladesh.

### How to submit changes:
1. **Fork** this repository to your GitHub account.
2. **Create a branch** for your work:
   ```bash
   git checkout -b feature/add-custom-qr-badge
   ```
3. **Commit your changes** with a clear, descriptive message:
   ```bash
   git commit -m "Add customizable badge colors for active QR accounts"
   ```
4. **Push your branch:**
   ```bash
   git push origin feature/add-custom-qr-badge
   ```
5. **Open a Pull Request** and tell us what you built!

Found an issue or have a feature idea? Open a ticket on our [Issues page](https://github.com/Oi-Technologies-Official/BanglaQR-Payment-Gateway-by-Oi/issues/new/choose).

---

## 📄 License

This project is licensed under the **MIT License** — free to use, modify, and distribute for personal and commercial projects.

See the [LICENSE](https://opensource.org/license/mit/) file for full details.

---

## 🏢 About Oi Technologies

**Oi Technologies** is passionate about building accessible, open-source tools that empower developers, store owners, and digital creators across Bangladesh.

- 🌐 **Website:** [oitech.com.bd](https://oitech.com.bd)
- 📧 **Need Help?** [support@oitech.com.bd](mailto:support@oitech.com.bd)
- 🔓 **Explore More Open Source Tools:** [oitech.com.bd/open-source](https://oitech.com.bd/open-source/)
- 💬 **Get in Touch:** [oitech.com.bd/helpline/contact-us](https://oitech.com.bd/helpline/contact-us/)

---

## ⭐ Support This Project

If **BanglaQR Payment Gateway** makes taking payments easier on your store, here is how you can support our open-source mission:

- ⭐ **Star this repository** on GitHub — it helps more Bangladeshi store owners discover it!
- 📢 **Share it** with friends, clients, or developer groups who build on WooCommerce.
- 💡 **Share your feedback:** Open an issue or suggestion to tell us how we can make it even more useful for your business.

---

<p align="center">
  <strong>Crafted with ❤️ for the Bangladeshi eCommerce Community 🇧🇩</strong><br>
  <em>Simplifying digital payments, one store at a time.</em>
</p>
