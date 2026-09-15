# Security Policy

## Supported Versions

Security updates are provided for the latest version of the BanglaQR Payment Gateway plugin. 

| Version | Supported          |
| ------- | ------------------ |
| 0.2.0   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within this plugin, please send an e-mail to Oi Technologies Support at **support@oitech.com.bd**. All security vulnerabilities will be promptly addressed.

Please do **not** report security vulnerabilities via public GitHub issues or WordPress support forums.

## Security Features Implemented

The **BanglaQR Payment Gateway** is built with strict adherence to WordPress and WooCommerce security standards.

### 1. File Upload Security
- **Nonce Verification:** Every AJAX upload request (`oi_banglaqr_upload_slip`) requires a valid WordPress nonce verified via `check_ajax_referer()`.
- **MIME Type Checking:** Uploads are strictly limited to image types (`image/jpeg`, `image/png`, `image/webp`, `image/gif`) using `wp_check_filetype()`.
- **Server-Side Validation:** The plugin verifies the file type and size using `wp_handle_upload` to prevent malicious scripts (e.g., `.php`, `.sh`) from being executed.

### 2. IDOR (Insecure Direct Object Reference) Protection
- **Temporary Meta Flag:** Uploaded images are flagged as `_oi_banglaqr_pending_upload` and tied specifically to the user's active session.
- **Order Attachment:** Images are only permanently attached and cleared of the temporary flag once a valid WooCommerce order is successfully placed and verified.
- **Cleanup:** Orphaned uploads (where an order is abandoned) are routinely ignored and can be safely purged.

### 3. Data Sanitization & Output Escaping
- **Input Sanitization:** All user inputs (e.g., Transaction IDs) are sanitized using `sanitize_text_field()` and `wp_unslash()` before being saved to the database.
- **Smart Validation:** Frontend inputs employ smart validation (stripping non-alphanumeric characters) to prevent XSS (Cross-Site Scripting) vectors.
- **Output Escaping:** All strings, URLs, and attributes rendered in the admin panel and checkout modal are escaped using WordPress native functions (`esc_html_e`, `esc_attr`, `esc_url`).

### 4. Privilege Checks
- Admin settings and AJAX handlers ensure that only users with the `manage_woocommerce` capability can modify the QR configurations or payment rules.
