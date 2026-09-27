<?php
/**
 * Image helpers.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether an image is a "cut-out": the product sits on a transparent or plain white
 * background (no photo backdrop), so it can float in the hero.
 *
 * Checks a ring of pixels along the image edges on the uncropped "medium" size. The result is
 * cached in the attachment's meta and recalculated when the file changes.
 *
 * @param int $attachment_id Attachment ID.
 */
function medhub_image_is_cutout( int $attachment_id ): bool {
	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return false;
	}

	$small = image_get_intermediate_size( $attachment_id, 'medium' );
	if ( $small && ! empty( $small['path'] ) ) {
		$uploads = wp_get_upload_dir();
		$path    = trailingslashit( $uploads['basedir'] ) . $small['path'];
		$file    = file_exists( $path ) ? $path : $file;
	}

	$key    = (string) filemtime( $file ) . ':' . filesize( $file );
	$cached = get_post_meta( $attachment_id, '_medhub_cutout', true );
	if ( is_array( $cached ) && ( $cached['key'] ?? '' ) === $key ) {
		return (bool) $cached['cutout'];
	}

	$cutout = medhub_image_edges_plain( $file );
	update_post_meta( $attachment_id, '_medhub_cutout', array( 'key' => $key, 'cutout' => $cutout ) );
	return $cutout;
}

/**
 * True when at least 92% of the edge pixels are transparent or near-white.
 *
 * @param string $file Image path.
 */
function medhub_image_edges_plain( string $file ): bool {
	if ( ! function_exists( 'imagecreatefromstring' ) ) {
		return false;
	}
	$data  = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$image = $data ? @imagecreatefromstring( $data ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors -- unsupported formats just count as "not a cut-out".
	if ( ! $image ) {
		return false;
	}

	$w     = imagesx( $image );
	$h     = imagesy( $image );
	$ring  = max( 1, (int) round( min( $w, $h ) * 0.02 ) ); // sample just inside the border
	$steps = 60;
	$plain = 0;
	$total = 0;

	$check = static function ( int $x, int $y ) use ( $image, &$plain, &$total ): void {
		$c     = imagecolorsforindex( $image, imagecolorat( $image, $x, $y ) );
		$clear = $c['alpha'] >= 100; // GD alpha: 0 opaque … 127 transparent
		$white = min( $c['red'], $c['green'], $c['blue'] ) >= 232 && ( max( $c['red'], $c['green'], $c['blue'] ) - min( $c['red'], $c['green'], $c['blue'] ) ) <= 14;
		++$total;
		if ( $clear || $white ) {
			++$plain;
		}
	};

	for ( $i = 0; $i <= $steps; $i++ ) {
		$x = (int) min( $w - 1, round( $i * ( $w - 1 ) / $steps ) );
		$y = (int) min( $h - 1, round( $i * ( $h - 1 ) / $steps ) );
		$check( $x, $ring - 1 );
		$check( $x, $h - $ring );
		$check( $ring - 1, $y );
		$check( $w - $ring, $y );
	}
	imagedestroy( $image );

	return $total > 0 && $plain / $total >= 0.92;
}
