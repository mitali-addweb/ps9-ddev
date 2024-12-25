<?php
/**
 * Clear PrestaShop cache for quicksetup template
 */

echo "<h1>PrestaShop Cache Clearer</h1>";
echo "<pre>";

$cache_files = array(
    '/opt/lampp/htdocs/prestashop_demo/var/cache/prod/smarty/compile/classiclayouts_layout_full_width_tpl/4a/d8/b6/4ad8b64142e78d4ea69b8e744cff84c6a591f139_2.module.tshirtecommerceviewstemplatesfrontquicksetup.tpl.php',
    '/opt/lampp/htdocs/prestashop_demo/var/cache/prod/smarty/compile/classiclayouts_layout_full_width_tpl/7e/df/37/7edf37972c9ed9be1a58ac3797e445b991a2db66_2.module.tshirtecommerceviewstemplatesfrontdesigner.tpl.php'
);

echo "Attempting to remove cached templates...\n\n";

$removed = 0;
$already_cleared = 0;

foreach ($cache_files as $cache_file) {
    $filename = basename($cache_file);
    echo "Checking: $filename\n";

    if (file_exists($cache_file)) {
        if (is_writable($cache_file)) {
            if (unlink($cache_file)) {
                echo "  ✓ SUCCESS: Removed!\n";
                $removed++;
            } else {
                echo "  ✗ FAILED: Could not remove\n";
            }
        } else {
            // Try to make it writable
            if (chmod($cache_file, 0666)) {
                if (unlink($cache_file)) {
                    echo "  ✓ SUCCESS: Removed after chmod!\n";
                    $removed++;
                } else {
                    echo "  ✗ Still could not remove\n";
                }
            } else {
                echo "  ✗ Could not change permissions\n";
            }
        }
    } else {
        echo "  ✓ Already cleared\n";
        $already_cleared++;
    }
    echo "\n";
}

echo "Summary:\n";
echo "  Removed: $removed files\n";
echo "  Already cleared: $already_cleared files\n";

echo "\n---\n\n";
echo "Now try reloading:\n";
echo "http://localhost/prestashop_demo/module/tshirtecommerce/designer\n";
echo "http://localhost/prestashop_demo/module/tshirtecommerce/quicksetup?step=0\n";

echo "</pre>";
?>
