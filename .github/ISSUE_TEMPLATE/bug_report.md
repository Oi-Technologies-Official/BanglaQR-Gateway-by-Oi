---
name: Bug report
about: Create a report to help us improve
title: Bug report
labels: ''
assignees: ''

---

**Describe the bug**
Uploaded payment receipts were accessible via direct URL without verifying ownership. Any user (including unauthenticated guests) who guessed or obtained a receipt file URL could view another customer's uploaded payment proof — a classic Insecure Direct Object Reference (IDOR) vulnerability.

**To Reproduce**
1. Complete a checkout as Customer A and upload a payment receipt.
2. Copy the direct URL of the uploaded receipt file (e.g., `.../uploads/banglaqr-receipts/abc123.jpg`).
3. Log out, or open the URL in a private/incognito window as an unrelated user.
4. Observe that the receipt image is displayed publicly without any ownership check.

**Expected behavior**
Uploaded receipts should be tied to the WooCommerce user session that created them. Only the uploading customer and shop administrators should be able to access the receipt file. Direct access by any other party should return `403 Forbidden`.

**Screenshots**
N/A — reported via code audit.

**Desktop (please complete the following information):**
 - OS: Windows 11
 - Browser: Chrome
 - Version: 120

**Smartphone (please complete the following information):**
 - Device: Any
 - OS: Any
 - Browser: Any
 - Version: Any

**Additional context**
**Severity:** High (Security)  
**Fix applied in:** v0.2.0  
Receipts are now tied to the WooCommerce user session, and access is validated server-side before serving the file.
