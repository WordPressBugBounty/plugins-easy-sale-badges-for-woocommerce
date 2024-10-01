<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Models;

defined( 'ABSPATH' ) || exit;

class BadgeModel extends BaseModel {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'asnp_wesb_badge';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats
	 *
	 * @since   1.0.0
	 *
	 * @return  array
	 */
	public function get_columns() {
		return array(
			'id'      => '%d',
			'title'   => '%s',
			'type'    => '%s',
			'status'  => '%d',
			'options' => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array();
	}

	public function add( array $args = array() ) {
		if ( isset( $args['id'] ) ) {
			$item = $this->get_item( $args['id'] );
			unset( $args['id'] );
			if ( $item ) {
				$this->update( $item->id, $args );
				return $item->id;
			}
		}

		$args = wp_parse_args( $args, $this->get_column_defaults() );
		$id   = $this->insert( $args, 'badge' );

		return $id ? $id : false;
	}

	public function get_item( $id, $output = OBJECT ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1", $id ), $output );
		if ( $item ) {
			$item = $this->get_item_options( $item );
		}

		return $item ? $item : false;
	}

	/**
	 * Get a collectoin of Badges.
	 *
	 * @param  array $args
	 * @return array
	 */
	public function get_items( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'number'   => 20,
				'offset'   => 0,
				'orderby'  => 'id',
				'order'    => 'ASC',
				'output'   => OBJECT,
				'paginate' => true,
			)
		);

		if ( $args['number'] < 1 ) {
			$args['number']   = 999999999999;
			$args['paginate'] = false;
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];
		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		$select_args = array();
		$where       = ' WHERE 1=1';

		// Specific conditions.
		if ( ! empty( $args['id'] ) ) {
			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map( 'absint', $args['id'] ) );
			} else {
				$ids = absint( $args['id'] );
			}
			$where .= " AND `id` IN( {$ids} )";
		}

		// Search by title.
		if ( ! empty( $args['title'] ) ) {
			$where        .= ' AND LOWER(`title`) LIKE %s';
			$select_args[] = '%' . $wpdb->esc_like( strtolower( sanitize_text_field( $args['title'] ) ) ) . '%';
		}

		// Status.
		if ( isset( $args['status'] ) ) {
			$where .= ' AND `status` = ' . intval( $args['status'] );
		}

		if ( ! empty( $args['paginate'] ) ) {
			$query = "SELECT COUNT(`$this->primary_key`) FROM {$this->table_name} {$where}";
			if ( ! empty( $select_args ) ) {
				$query = $wpdb->prepare( "SELECT COUNT(`$this->primary_key`) FROM {$this->table_name} {$where}", $select_args );
			}

			$total = (int) $wpdb->get_var( $query );
			if ( 0 >= $total ) {
				return [
					'items' => [],
					'total' => 0,
					'pages' => 0,
				];
			}
		}

		$select_args[] = absint( $args['offset'] );
		$select_args[] = absint( $args['number'] );

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", $select_args ), $args['output'] );

		if ( ! empty( $items ) ) {
			for ( $i = 0; $i < count( $items ); $i++ ) {
				$items[ $i ] = $this->get_item_options( $items[ $i ] );
			}
		}

		if ( empty( $args['paginate'] ) ) {
			return $items;
		}

		return [
			'items' => $items,
			'total' => absint( $total ),
			'pages' => ceil( absint( $total ) / absint( $args['number'] ) ),
		];
	}

	public function delete( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$item = $this->get_item( $id );
		if ( 0 < $item->id ) {
			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $item->id ), array( '%d' ) );
		}

		return false;
	}

	public function duplicate( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1", $id ), ARRAY_A );
		if ( ! $item ) {
			return false;
		}

		unset( $item['id'] );
		$item['title'] = sprintf( '%s (Copy)', sanitize_text_field( $item['title'] ) );

		return $this->add( $item );
	}

	public function get_item_options( $item ) {
		if ( ! $item ) {
			return $item;
		}

		if ( is_object( $item ) ) {
			if ( ! empty( $item->options ) ) {
				$item->options = maybe_unserialize( $item->options );
				foreach ( $item->options as $key => $value ) {
					if ( ! isset( $item->{$key} ) ) {
						$item->{$key} = $value;
					}
				}
			}
			unset( $item->options );
		} elseif ( isset( $item['id'] ) ) {
			if ( ! empty( $item['options'] ) ) {
				$item['options'] = maybe_unserialize( $item['options'] );
				foreach ( $item['options'] as $key => $value ) {
					if ( ! isset( $item[ $key ] ) ) {
						$item[ $key ] = $value;
					}
				}
			}
			unset( $item['options'] );
		}

		return $item;
	}

}
