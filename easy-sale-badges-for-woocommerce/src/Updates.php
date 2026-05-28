<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Updates;

function update_720() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'asnp_wesb_badge';
    
    $column_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} LIKE %s",
            'ordering'
        )
    );
    
    if ( empty( $column_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$table_name} ADD COLUMN ordering bigint(20) unsigned NOT NULL DEFAULT 0"
            )
        );
    }
}
