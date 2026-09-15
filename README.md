# 💳 BanglaQR Payment Gateway for BanglaQR

![banglaqr-payment-gateway-banglaqr](assets/banner-1544x500.png)

A WooCommerce payment gateway supporting bank and mobile QR payments with a scan-to-pay popup and payment receipt upload verification. Aligned with Bangladesh's national QR payment standard, this plugin enables merchants to accept payments from any bank app or mobile financial services (MFS) including bKash, Nagad, Rocket, Upay, CellFin, and bank transfers.
---

## 📌 Plugin Information
- **Contributors:** Oi Technologies
- **Tags:** woocommerce, payment, gateway, banglaqr, qrpayment
- **Requires at least:** WordPress 5.6
- **Tested up to:** WordPress 7.0 
- **Requires PHP:** 7.4  
- **Stable tag:** 0.2.0
- **License:** [MIT](https://opensource.org/license/mit/)

---

## 📖 Description
BanglaQR Payment Gateway for BanglaQR is a payment gateway for WooCommerce websites operating in Bangladesh. Built to support the unified **BanglaQR** national standard launched by Bangladesh Bank, this plugin enables merchants to accept payments from any bank app or mobile financial services (MFS) including **bKash, Nagad, Rocket, Upay, CellFin, and bank transfers**.

During checkout, customers receive a scan-to-pay QR popup modal. They can scan the QR code from their mobile apps, complete the payment, and upload their payment screenshot or receipt before checking out. 

Backend administrators can configure multiple QR codes, assign customized processing bank fees/percentage charges per QR, and check customer-uploaded payment receipts inside the WooCommerce order review screen.
---

## ✨ Features
- **BanglaQR Integration:** Aligned with Bangladesh's national QR payment standard.
- **MFS & Bank Apps Compatibility:** Works with bKash, Nagad, Rocket, Upay, CellFin, City Touch, and bank apps.
- **Popup Modal:** A responsive checkout popup that displays the payable amount, active QR, and instructions.
- **Per-QR Percentage Charges:** Define separate bank fees or processing charges (e.g., 1.5% for credit cards, 0.7% for bKash) dynamically calculated and added to the checkout total.
- **Proof of Payment Upload:** Customers can upload screenshots or receipts directly inside the checkout popup.
- **Automated Image Optimization:** Uploaded receipts are auto-rotated, scaled to 1200px, and compressed to 70% quality on-the-fly.
- **Manual Payment Accounts:** Fallback manual payment numbers (bKash, Nagad, Rocket, Upay, CellFin) with one-click copy buttons matching brand colors.
- **Smart Checkout Rules:** Configure whether Receipt Upload and Transaction ID fields are mandatory, optional, or hidden.
- **Transaction ID Smart Validation:** Automatically sanitizes TrxID inputs to enforce uppercase alphanumeric format.
- **Enhanced UI/UX:** Zoomable receipt previews, custom theme-inherited fonts, countdown timer resets, and a beautiful pulsing success animation.
- **Drag-and-Drop QR Manager:** Sort and prioritize multiple QR accounts using a sortable settings panel.
- **Settings Access Shortcut:** Quick "Settings" shortcut link on the WordPress plugin lists page.
- **Attachment Preview:** Preview and verify uploaded receipt slips inside the order details page.

---

## ⚙️ Installation
- Upload the `banglaqr-payment-gateway-banglaqr` folder to the `/wp-content/plugins/` directory.
- Activate the plugin through the 'Plugins' menu in WordPress.
- Go to WooCommerce -> Settings -> Payments -> Bangla QR Payment.
- Configure your QR accounts and details.

---

## ❓ Frequently Asked Questions

### 🔹 Do I need a BanglaQR?
Yes. You can generate and use your official BanglaQR code (or any bank/MFS QR code) inside the QR accounts manager.

### 🔹 Can I charge extra fees for payment methods?
Yes. You can define a customized percentage charge (e.g., 1.85% for bKash, 0% for Bank transfers) inside each QR account settings. The plugin dynamically calculates the charge based on the cart total and updates the payment details in real time.

### 🔹 Where do I verify the customer-uploaded screenshots?
Shop administrators can view and download the uploaded receipt proofs directly under the "Order Details" panel in the WooCommerce -> Orders page in the WordPress admin panel.

### 🔹 Does it support guest checkout?
Yes, guest customers can checkout and upload their payment screenshots securely.

---

## 🖼️ Screenshots
1. QR Accounts Manager. ![QR Accounts Manager](assets/screenshot-1.png)
2. Gateway Settings. ![Gateway Settings](assets/screenshot-2.png)
3. Checkout payment popup. ![Checkout payment popup](assets/screenshot-3.png)
4. Checkout page payment method. ![Checkout page payment method](assets/screenshot-4.png)
5. Customer payment receipt/screenshot. ![Customer payment receipt/screenshot](assets/screenshot-5.png)

---

## ⚖️ License & Copyright
- Copyright © **Oi Technologies** 
- Email: [support@oitech.com.bd](mailto:support@oitech.com.bd)
- Website: https://oitech.com.bd
- Open Source: https://oitech.com.bd/open-source/
- Contact: https://oitech.com.bd/helpline/contact-us/
- Licensed under the **MIT License**  
- ✅ This plugin is **free to use, modify, and distribute** under the license terms.
