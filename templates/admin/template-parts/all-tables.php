<?php
$mpc_opt_sc = new MPCShortcode();
$mpc_opt_sc->change_shortcode();

echo '<div class="mpcdp_settings_section">';
$mpc_opt_sc->table_list();
echo '</div>';
