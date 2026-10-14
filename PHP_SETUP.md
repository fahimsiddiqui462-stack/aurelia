# Aurelia Athletic Club — PHP + jQuery setup

This version uses `submit.php`, `admin.php`, CSV storage, PHP `mail()`, and jQuery AJAX. It is intended for InfinityFree, shared Apache/PHP hosting, or another PHP-enabled host.

## Upload
Upload the complete gym folder to the public web directory. Keep `submit.php`, `admin.php`, `styles.css`, `script.js`, and `assets/` together. PHP 7.4+ is recommended.

## Form behavior
- The form on `contact.html` sends to `submit.php` with jQuery AJAX.
- The request is appended to `data/leads.csv`.
- PHP attempts to send a notification using the host's `mail()` function.
- The response opens a pre-filled WhatsApp message using the configured demo number.
- Local preview without PHP uses the WhatsApp fallback in `script.js`.

## Admin
Open `/admin.php`. The current demo password is `aurelia-demo-2026`.

Before publishing, change `$admin_password` at the top of `admin.php`. Also replace the `$config` values at the top of `submit.php` with the actual club email and WhatsApp number.

## Email limitation
Some free hosts disable PHP `mail()` or require a mailbox on the hosting domain. The form still saves leads and opens WhatsApp if email delivery is unavailable. Verify delivery on the chosen host with a real test submission.

The Netlify Functions files are still present as an alternate deployment option, but this PHP version should be used for InfinityFree/shared PHP hosting.
