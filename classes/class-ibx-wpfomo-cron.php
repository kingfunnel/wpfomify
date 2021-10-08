<?php
/**
 * Helper class for cron jobs.
 *
 * @since 1.1.2
 */
class IBX_WPFomo_Cron {
	/**
	 * Holds cron hook.
	 *
	 * @since 1.1.2
	 * @var string $hook
	 */
	public static $hook = 'ibx_wpfomo_cron_update_data';

	/**
	 * Schedule cron jobs.
	 *
	 * @since 1.1.2
	 * @param int $post_id
	 */
	public static function set_cron( $post_id ) {
		if ( ! $post_id || empty( $post_id ) ) {
			return;
		}

		// First clear previously scheduled cron hook.
		wp_clear_scheduled_hook(
			self::$hook,
			array(
				'post_id' => $post_id,
				'is_cron' => true,
			)
		);

		// If there is no next event, start cron now.
		if ( ! wp_next_scheduled(
			self::$hook,
			array(
				'post_id' => $post_id,
			)
		) ) {
			wp_schedule_event(
				time(),
				'wpfomify_cache_interval',
				self::$hook,
				array(
					'post_id' => $post_id,
					'is_cron' => true,
				)
			);
		}
	}
}
