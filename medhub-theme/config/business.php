<?php
/**
 * Central business details: the ONLY place these values live.
 *
 * Every entry has a status:
 *   'placeholder'  – unknown or conflicting; never shown publicly.
 *   'site-sourced' – copied from the current medhub.ae; shown on local/staging only,
 *                    with an admin-only "to confirm" marker.
 *   'confirmed'    – confirmed by MedHub; shown everywhere.
 *
 * Templates must read these through medhub_business() / medhub_business_can_show()
 * (inc/helpers/business.php), never hardcode them.
 *
 * Open questions refer to docs/stage-2-plan.md §8 (Q1–Q19).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

return array(

	'brand_name'   => array(
		'value'  => 'MedHub',
		'status' => 'site-sourced',
		'note'   => 'Q14: MedHub vs Life Choice.',
	),

	'legal_name'   => array(
		'value'  => 'LIFE CHOICE MEDICAL EQUIPMENT TRADING L.L.C',
		'status' => 'confirmed',
		'source' => 'Footer + Rank Math organisation schema.',
	),

	'phone'        => array(
		'value'  => '+971 52 814 2931',
		'status' => 'confirmed',
		'source' => 'Live medhub.ae footer + call links (confirmed by user 26 Sep 2026: "same as live").',
		'note'   => 'Rank Math schema still says +971 50 255 2219 (Rank Math → Titles & Meta → Local SEO).',
	),

	// Second number, shown on the contact page only (live contact page: "Mobile: +971 4 252 3424").
	'phone_alt'    => array(
		'value'  => '+971 4 252 3424',
		'status' => 'confirmed',
		'source' => 'Live /contact-us-medhub/.',
	),

	'whatsapp'     => array(
		'value'  => '971528142931', // International format without "+".
		'status' => 'confirmed',
		'source' => 'Live medhub.ae WhatsApp link (wa.me/971528142931).',
	),

	'email'        => array(
		'value'  => 'lifechoicemed@gmail.com',
		'status' => 'confirmed',
		'source' => 'Live medhub.ae footer and contact page.',
	),

	'address'      => array(
		'value'  => array(
			'line1'    => 'Shop No. 1 B, Makateb Building',
			'line2'    => 'Airport Road, opposite Nissan Showroom, Port Saeed',
			'locality' => 'Deira, Dubai',
			'country'  => 'United Arab Emirates',
		),
		'status' => 'confirmed',
		'source' => 'Live medhub.ae footer and contact page.',
	),

	'map_url'      => array(
		'value'  => 'https://www.google.com/maps/search/?api=1&query=25.258065901688465,55.33272774559436',
		'status' => 'confirmed',
		'source' => 'Map pin in the live Rank Math Local SEO schema.',
	),

	'hours'        => array(
		'value'  => null,
		'status' => 'placeholder',
		'note'   => 'Q4: only present in schema today (Mon–Sat, split Fri/Sat). Sunday unknown.',
	),

	'socials'      => array(
		'value'  => array(
			'facebook'  => 'https://www.facebook.com/medhub.uae',
			'instagram' => 'https://www.instagram.com/medhub_uae',
			'tiktok'    => 'https://www.tiktok.com/@medhub.ae',
		),
		'status' => 'confirmed',
	),

	'delivery'     => array(
		'value'  => array(
			'processing' => 'within 24 hours of payment confirmation',
			'fee'        => 'AED 25',
			'timeline'   => '2–3 business days',
			'region'     => 'United Arab Emirates only',
		),
		'status' => 'site-sourced',
		'source' => '/delivery-policy/',
		'note'   => 'Q6: confirm these are still current.',
	),

	'rental_terms' => array(
		'value'  => null,
		'status' => 'placeholder',
		'note'   => 'Q7: minimum period, deposit, delivery/collection.',
	),

	'payment'      => array(
		'value'  => 'Secure card payment',
		'status' => 'site-sourced',
		'source' => 'Telr gateway + "Accepted cards" footer.',
		'note'   => 'Q16: any other methods (COD, BNPL)?',
	),

	'support_24_7' => array(
		'value'  => null,
		'status' => 'placeholder',
		'note'   => 'Q11: not claimed until confirmed.',
	),

	'medicines_note' => array(
		'value'  => 'MedHub does not sell medicines.',
		'status' => 'site-sourced',
		'source' => 'About page ("NOTE: We are not selling any medicine.").',
	),

	// Contact form adapter (Q17). Provider: 'wpforms' | 'cf7' | null.
	// With null, the contact page shows call / WhatsApp / email actions instead of a form.
	'contact_form' => array(
		'value'  => array(
			'provider' => null,
			'id'       => null,
		),
		'status' => 'placeholder',
		'note'   => 'Q17: WPForms is installed on the live site; CF7 is not active.',
	),
);
