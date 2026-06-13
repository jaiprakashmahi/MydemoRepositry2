Admin standardization applied:
- Rebuilt common menu.php with consistent header/sidebar navigation.
- Added css/admin-standard.css for fixed navbar/sidebar/card/table/form design.
- Added js/admin-standard.js for active menu highlighting.
- Fixed common typos: main-wrapper id, container-fluid, viewport width.
- Added fetch_block.php and improved admin create-center flow to load blocks after district.
- Rewrote insert_center.php to save state/district/block IDs and fixed payment_mode bug.

Note: Existing database rows with text state/district should be converted to numeric IDs for full consistency.
