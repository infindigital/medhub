<?php
/**
 * Site footer and document close.
 * The designed footer arrives in step 2D.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<?php
	$medhub_legal = medhub_business( 'legal_name' );
	if ( $medhub_legal ) {
		printf( '<p>&copy; %1$s %2$s</p>', esc_html( gmdate( 'Y' ) ), esc_html( $medhub_legal ) );
	}
	?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
