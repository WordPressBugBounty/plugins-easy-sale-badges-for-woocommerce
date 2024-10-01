<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Validator;

defined( 'ABSPATH' ) || exit;

class DateTimeValidator {

	/**
	 * Checking is valid given date times.
	 *
	 * @param  array  $date_times
	 *
	 * @return boolean
	 */
	public static function is_valid_date_times( array $date_times ) {
		if ( empty( $date_times ) ) {
			return true;
		}

		$empty = true;
		foreach ( $date_times as $group ) {
			if ( empty( $group ) ) {
				continue;
			}

			$empty = false;
			$valid = true;
			foreach ( $group as $date_time ) {
				if ( ! static::is_valid( $date_time ) ) {
					$valid = false;
					break;
				}
			}
			if ( $valid ) {
				return true;
			}
		}
		return $empty;
	}

	/**
	 * Is valid given date time.
	 *
	 * @param  array $date_time
	 *
	 * @return boolean
	 */
	public static function is_valid( array $date_time ) {
		if ( empty( $date_time ) || empty( $date_time['type'] ) ) {
			return false;
		}

		if ( in_array( $date_time['type'], array( 'date', 'dateTime' ) ) ) {
			if ( empty( $date_time['start'] ) && empty( $date_time['end'] ) ) {
				return false;
			}

			$format = 'dateTime' === $date_time['type'] ? 'Y-m-d H:i' : 'Y-m-d';
			$now    = strtotime( date( $format, current_time( 'timestamp' ) ) );

			if ( ! empty( $date_time['start'] ) ) {
				$start_date = strtotime( date( $format, strtotime( $date_time['start'] ) ) );
				if ( false === $start_date || $now < $start_date ) {
					return false;
				}
			}

			if ( ! empty( $date_time['end'] ) ) {
				$end_date = strtotime( date( $format, strtotime( $date_time['end'] ) ) );
				if ( false === $end_date || $now > $end_date ) {
					return false;
				}
			}

			return true;
		} elseif ( 'specificDate' === $date_time['type'] ) {
			if ( ! empty( $date_time['date']['time'] ) ) {
				$dates = array_map( 'trim', explode( ',', trim( $date_time['date']['time'], '[]' ) ) );
				if ( ! empty( $dates ) ) {
					$now = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );
					foreach ( $dates as $date ) {
						$date = strtotime( trim( $date, '"' ) );
						if ( $now == $date ) {
							return true;
						}
					}
				}
			}
		} elseif ( 'time' === $date_time['type'] ) {
			if ( empty( $date_time['startTime'] ) || empty( $date_time['endTime'] ) ) {
				return false;
			}

			$now        = current_time( 'timestamp' );
			$start_date = strtotime( $date_time['startTime'], $now );
			$end_date   = strtotime( $date_time['endTime'], $now );

			// If endTime is less than startTime, add 24 hours to endTime
			if ( $end_date < $start_date ) {
				$end_date = strtotime( '+1 day', $end_date );
			}

			return ( $now >= $start_date && $now <= $end_date );
		} elseif ( 'days' === $date_time['type'] ) {
			if ( ! empty( $date_time['days'] ) ) {
				$today = date( 'l', current_time( 'timestamp' ) );
				foreach ( $date_time['days'] as $day ) {
					if ( is_array( $day ) ) {
						if ( isset( $day['value'] ) && $day['value'] === $today ) {
							return true;
						}
					} elseif ( $today == $day ) {
						return true;
					}
				}
			}
		}

		return false;
	}

}
