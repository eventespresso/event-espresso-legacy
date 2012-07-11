<?php
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
event_espresso_require_gateway("alipay/index.php");