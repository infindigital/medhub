/**
 * CONTENT DRAFTS – proposed page copy for review by MedHub.
 *
 * Imported ONLY into the dev sandbox (tooling/sandbox/seed.php "content"). On the real
 * site this copy is entered/approved in WordPress (block editor) – never hardcoded in the theme.
 *
 * Sources for every factual statement:
 *  - catalogue data (products, brands, categories, rental items) – dev-seed.json snapshot
 *  - live policies: delivery (AED 25, 24 h processing, 2–3 business days, UAE only),
 *    refunds (2 days, unopened/defective, 10–45 working days), cancellations (before shipment),
 *    terms (Visa/MasterCard, AED)
 *  - live About/Home copy: customer types, local support, "not selling any medicine"
 *  - live DeVilbiss service page: equipment list (no "authorised" claim, no 24/7 claim)
 * General equipment explanations avoid outcome claims and defer settings to the clinician.
 */
import { B } from './blocks.mjs';

const DELIVERY_QA = [
	'How much does delivery cost?',
	'Standard delivery within the UAE costs AED 25. Orders are processed within 24 hours of payment confirmation and delivered within 2–3 business days.',
];
const RENT_CTA = '/product-category/medical-equipment-rental/';

/* ------------------------------------------------------------------------- */
/* Pages (existing live URLs)                                                 */
/* ------------------------------------------------------------------------- */
export const pages = {
	'medical-equipment': {
		title: 'About Us',
		keyword: 'Medical Equipment Suppliers in Dubai',
		sections: [
			B.hero({
				eyebrow: 'About MedHub',
				heading: 'About MedHub, *medical equipment suppliers* in Dubai',
				lead: 'MedHub supplies oxygen, sleep, respiratory and patient-care equipment to hospitals, clinics, healthcare professionals and families caring for someone at home, from a shop in Port Saeed, Deira.',
				primaryLabel: 'Explore the catalogue',
				primaryUrl: '/shop/',
				secondaryLabel: 'Contact MedHub',
				secondaryUrl: '/contact-us-medhub/',
			}),
			B.trust(),
			B.bento({
				eyebrow: 'What we do',
				heading: 'Supply, rental and *service support.*',
				cells: [
					{ cls: 'is-feature', h: 'Equipment supply', p: 'One catalogue for oxygen therapy, sleep care, respiratory care, hospital furniture, monitoring and everyday medical supplies, from recognised brands.' },
					{ h: 'Monthly rental', p: 'Oxygen concentrators, CPAP machines, electric beds, suction units, feeding pumps and monitors can be rented by the month.' },
					{ h: 'Service support', p: 'Repair and maintenance for DeVilbiss respiratory equipment through the MedHub service centre.' },
					{ cls: 'is-dark', h: 'Local support', p: 'We help customers choose the right equipment and provide local support before and after purchase.' },
				],
			}),
			B.explorer({ eyebrow: 'What we supply', heading: 'Medical equipment in *eight departments.*' }),
			B.brands({ eyebrow: 'Brands', heading: 'Brands in *our range*', limit: 12 }),
			B.service({ eyebrow: 'Ordering', heading: 'Ordering and *delivery*' }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'About *MedHub*',
				items: [
					['Where is MedHub?', 'Our shop is at Shop No. 1 B, Makateb Building, Airport Road (opposite the Nissan showroom), Port Saeed, Deira, Dubai.'],
					['Who do you supply?', 'Hospitals, clinics and healthcare professionals, as well as individuals and families buying equipment for use at home.'],
					['Can I rent equipment instead of buying it?', 'Yes. Selected equipment, including oxygen concentrators, CPAP machines, electric beds and patient monitors, is available on monthly rental.'],
					['Do you sell medicines?', 'No. MedHub supplies medical equipment and supplies, not medicines.'],
					['Which company operates MedHub?', 'medhub.ae is operated by LIFE CHOICE MEDICAL EQUIPMENT TRADING L.L.C, Deira, Dubai.'],
				],
			}),
			B.cta({ eyebrow: 'Get in touch', heading: 'Talk to MedHub *before* you buy.', text: 'Tell us what you need and we will help you compare options, whether you are buying or renting.', primaryLabel: 'Shop Medical Equipment', primaryUrl: '/shop/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'cpap-in-dubai': {
		title: 'CPAP in Dubai',
		keyword: 'CPAP in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Sleep care',
				heading: 'CPAP machines in Dubai',
				lead: 'Buy or rent a CPAP in Dubai: auto and travel CPAP machines, masks and accessories for sleep apnea therapy at home, from MedHub in Deira.',
				primaryLabel: 'Shop CPAP machines',
				primaryUrl: '/product-category/auto-cpap-machine/',
				secondaryLabel: 'Rent a CPAP',
				secondaryUrl: RENT_CTA,
				categories: 'auto-cpap-machine,cpap,travel-cpap-machine',
			}),
			B.links({
				eyebrow: 'Choose by type',
				heading: 'Everything for *CPAP therapy*',
				items: [
					'category:auto-cpap-machine',
					'category:cpap | CPAP machines',
					'category:travel-cpap-machine | Travel CPAP machines',
					'category:buy-cpap-bipap-masks | CPAP and BiPAP masks',
					'category:cpap-bipap-accessories | Filters, tubing and humidifiers',
					'product:resmed-airsense-10-autoset-with-heated-humidifier-and-mask-rental | Rent an auto CPAP by the month',
					'brand:resmed | ResMed masks and accessories',
					'page:bipap-in-dubai | BiPAP machines',
					'page:sleep-apnea-machine-in-dubai | All sleep apnea machines',
				],
			}),
			B.products({ eyebrow: 'Available now', heading: 'CPAP machines', categories: 'auto-cpap-machine,cpap,travel-cpap-machine', limit: 4, linkLabel: 'See all auto CPAP' }),
			B.bento({
				eyebrow: 'How to choose',
				heading: 'Four things to *check first*',
				layout: 'cards',
				cells: [
					{ h: 'Fixed or auto pressure', p: 'A fixed CPAP delivers one set pressure. An auto CPAP adjusts within a range during the night. The pressure or range comes from your sleep specialist.' },
					{ h: 'Humidifier', p: 'Many machines include a heated humidifier, which some people find more comfortable in air-conditioned bedrooms.' },
					{ h: 'Mask fit', p: 'Nasal, nasal pillow and full face masks suit different breathing and sleeping habits. A good fit matters as much as the machine.' },
					{ h: 'Travel', p: 'Compact travel CPAP machines are smaller and lighter. Check the power options before you fly.' },
				],
			}),
			B.service({ eyebrow: 'Buying from MedHub', heading: 'Delivery and *support*' }),
			B.posts({ eyebrow: 'Guides', heading: 'CPAP *guides*', category: 'cpap-and-bipap', count: 3 }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'CPAP in Dubai: *questions*',
				items: [
					['Which pressure settings should I use?', 'Pressure settings come from your doctor or sleep specialist. We can help you choose a machine and mask that suit the prescription you have.'],
					['Can I rent a CPAP machine?', 'Yes. An auto CPAP machine with humidifier is available on monthly rental. The monthly price is shown on the rental product page.'],
					['What is the difference between CPAP and BiPAP?', 'A CPAP delivers one pressure. A BiPAP delivers a higher pressure when you breathe in and a lower one when you breathe out. Your clinician decides which you need.'],
					['Do you stock masks and replacement parts?', 'Yes. Nasal, nasal pillow and full face masks, plus filters, tubing and humidifier parts for common machines.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Not sure which CPAP?', heading: 'Compare CPAP options *with us.*', text: 'Tell us about your prescription and how you sleep, and we will suggest machines and masks that fit.', primaryLabel: 'Shop CPAP machines', primaryUrl: '/product-category/auto-cpap-machine/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'bipap-in-dubai': {
		title: 'BiPAP in Dubai',
		keyword: 'BiPAP in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Sleep & respiratory care',
				heading: 'BiPAP machines in Dubai',
				lead: 'Looking for BiPAP in Dubai? Auto BiPAP, BiPAP ST and AVAPS machines from Philips, ResMed and BMC, with masks and accessories, available from MedHub in Deira.',
				primaryLabel: 'Shop BiPAP machines',
				primaryUrl: '/product-category/bipap/',
				secondaryLabel: 'Auto BiPAP',
				secondaryUrl: '/product-category/auto-bipap-machine/',
				categories: 'bipap,auto-bipap-machine',
			}),
			B.products({ eyebrow: 'Available now', heading: 'BiPAP machines', categories: 'bipap,auto-bipap-machine', limit: 4, linkLabel: 'See all BiPAP' }),
			B.bento({
				eyebrow: 'Understanding BiPAP',
				heading: 'Modes and features, *in plain words*',
				layout: 'cards',
				cells: [
					{ h: 'Two pressures', p: 'A BiPAP uses a higher pressure as you breathe in and a lower one as you breathe out. The values are set by your clinician.' },
					{ h: 'Auto BiPAP', p: 'Adjusts pressures within a prescribed range during the night, instead of staying at fixed values.' },
					{ h: 'ST and AVAPS modes', p: 'Some machines add a backup breath rate (ST) or target a breath volume (AVAPS). Your clinician chooses the mode and settings.' },
				],
			}),
			B.links({
				eyebrow: 'Related',
				heading: 'Masks, parts and *other machines*',
				items: [
					'category:buy-cpap-bipap-masks | CPAP and BiPAP masks',
					'category:cpap-bipap-accessories | BiPAP accessories',
					'brand:philips | Philips Respironics',
					'brand:resmed | ResMed',
					'page:cpap-in-dubai | CPAP machines',
					'page:sleep-apnea-machine-in-dubai | All sleep apnea machines',
				],
			}),
			B.posts({ eyebrow: 'Guides', heading: 'BiPAP *guides*', category: 'cpap-and-bipap', count: 3 }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'BiPAP in Dubai: *questions*',
				items: [
					['What is the difference between BiPAP and CPAP?', 'A CPAP delivers one pressure all night. A BiPAP switches between a higher inhale pressure and a lower exhale pressure. Your clinician decides which you need.'],
					['What do ST and AVAPS mean?', 'ST (spontaneous/timed) adds a backup breath rate. AVAPS adjusts pressure to reach a target breath volume. Both are set by your clinician.'],
					['Which BiPAP brands do you stock?', 'The current range includes Philips DreamStation, ResMed Lumis and BMC machines. Check each product page for live price and stock.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Questions about BiPAP?', heading: 'Choose the right machine *with us.*', text: 'Share your prescription and we will help you compare BiPAP models and masks.', primaryLabel: 'Shop BiPAP machines', primaryUrl: '/product-category/bipap/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'oxygen-concentrator-in-dubai': {
		title: 'Oxygen Concentrator in Dubai',
		keyword: 'oxygen concentrator in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Oxygen therapy',
				heading: 'Oxygen concentrators in Dubai',
				lead: 'Choosing an oxygen concentrator in Dubai? Home oxygen concentrators from Philips, Yuwell and DeVilbiss in 5L and 10L models, plus portable units, accessories and monthly rental.',
				primaryLabel: 'Shop oxygen concentrators',
				primaryUrl: '/product-category/buy-oxygen-concentrator/',
				secondaryLabel: 'Rent monthly',
				secondaryUrl: RENT_CTA,
				categories: 'buy-oxygen-concentrator',
			}),
			B.products({ eyebrow: 'Home concentrators', heading: 'Available *now*', categories: 'buy-oxygen-concentrator', limit: 4, linkLabel: 'See all home concentrators' }),
			B.bento({
				eyebrow: 'How to choose',
				heading: 'Match the machine to *the prescription*',
				layout: 'cards',
				cells: [
					{ h: '5L or 10L', p: 'The number is the highest flow in litres per minute. Choose a model that covers the flow your doctor prescribed.' },
					{ h: 'Home or portable', p: 'Home units run on mains power. Portable concentrators use batteries for time away from home.' },
					{ h: 'Buy or rent', p: 'Philips Everflo 5L and DeVilbiss 1025 10L concentrators are available on monthly rental.' },
					{ h: 'Accessories', p: 'Nasal cannulas, humidifier bottles, connectors and sterile water keep the setup running.' },
				],
			}),
			B.links({
				eyebrow: 'Explore oxygen therapy',
				heading: 'Related *equipment*',
				items: [
					'category:portable-oxygen-concentrator | Portable oxygen concentrators',
					'category:oxygen-concentrator-accessories | Oxygen concentrator accessories',
					'product:philips-everflo-5-ltr-oxygen-concentrator-rental | Rent a Philips Everflo 5L',
					'product:devilbiss-compact-1025-10-ltr-oxygen-concentrator-rental | Rent a DeVilbiss 1025 10L',
					'product:oxygen-cylinder-in-dubai | Oxygen cylinder rental',
					'brand:philips | Philips Respironics',
					'brand:yuwell | Yuwell',
					'page:portable-oxygen-machine-in-dubai | Portable oxygen machines',
					'page:oxygen-machine-in-dubai | Which oxygen machine fits?',
				],
			}),
			B.posts({ eyebrow: 'Guides', heading: 'Oxygen *guides*', category: 'oxygen-machine', count: 3 }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'Oxygen concentrator in Dubai: *questions*',
				items: [
					['What is the difference between a 5L and a 10L concentrator?', 'A 5L unit delivers up to 5 litres per minute and a 10L unit up to 10. Use the flow rate on your prescription to choose.'],
					['Can I rent an oxygen concentrator?', 'Yes. The Philips Everflo 5L and DeVilbiss Compact 1025 10L are available on monthly rental. A portable concentrator is also available to rent.'],
					['Do you sell Philips oxygen concentrators?', 'Yes. The range currently includes the Philips Respironics Oxygenate 5, and the Philips Everflo 5L is available to rent.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Need oxygen at home?', heading: 'Find the right concentrator *with us.*', text: 'Share the flow rate you need and we will suggest home, portable or rental options.', primaryLabel: 'Shop concentrators', primaryUrl: '/product-category/buy-oxygen-concentrator/', secondaryLabel: 'Rent equipment', secondaryUrl: RENT_CTA }),
		],
	},

	'portable-oxygen-machine-in-dubai': {
		title: 'Portable Oxygen Machine in Dubai',
		keyword: 'Portable oxygen machine in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Portable oxygen',
				heading: 'Portable oxygen machines in Dubai',
				lead: 'Looking for a portable oxygen machine in Dubai? Battery-powered oxygen concentrators from Inogen, Caire, GCE and O2 Concepts for everyday life and travel, to buy or rent by the month.',
				primaryLabel: 'Shop portable concentrators',
				primaryUrl: '/product-category/portable-oxygen-concentrator/',
				secondaryLabel: 'Rent monthly',
				secondaryUrl: RENT_CTA,
				categories: 'portable-oxygen-concentrator',
			}),
			B.products({ eyebrow: 'Portable concentrators', heading: 'Available *now*', categories: 'portable-oxygen-concentrator', limit: 4, linkLabel: 'See all portable concentrators' }),
			B.bento({
				eyebrow: 'How to choose',
				heading: 'What to compare *before you buy*',
				layout: 'cards',
				cells: [
					{ h: 'Pulse or continuous flow', p: 'Most portable units deliver oxygen in pulses as you breathe in. Some also offer continuous flow. Your prescription tells you which you need.' },
					{ h: 'Battery time', p: 'Battery life depends on the battery size and the flow setting. Each product page lists the manufacturer figures.' },
					{ h: 'Size and weight', p: 'Lighter units are easier to carry every day. Larger ones usually offer higher settings.' },
					{ h: 'Flying', p: 'Airlines set their own rules for portable oxygen concentrators. Check with your airline and carry enough battery power.' },
				],
			}),
			B.links({
				eyebrow: 'Related',
				heading: 'Brands, accessories and *rental*',
				items: [
					'brand:inogen | Inogen',
					'category:oxygen-concentrator-accessories | Oxygen accessories',
					'product:inogen-g5-portable-oxygen-concentrator-with-16cell-battery-rental | Rent a portable concentrator',
					'page:oxygen-concentrator-in-dubai | Home oxygen concentrators',
					'page:oxygen-machine-in-dubai | Which oxygen machine fits?',
				],
			}),
			B.posts({ eyebrow: 'Guides', heading: 'Portable oxygen *guides*', category: 'oxygen-machine', count: 3 }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'Portable oxygen machine in Dubai: *questions*',
				items: [
					['Can I rent a portable oxygen concentrator?', 'Yes. A portable oxygen concentrator is available on monthly rental. The monthly price is on the rental product page.'],
					['Which brands do you stock?', 'The current range includes Inogen, Caire, GCE and O2 Concepts. Check each product page for live price and stock.'],
					['Can I take a portable concentrator on a flight?', 'Airlines decide which devices are allowed and how much battery you must carry. Check with your airline before you travel.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Planning to travel?', heading: 'Choose a portable unit *with us.*', text: 'Tell us your flow setting and how long you need between charges, and we will suggest models.', primaryLabel: 'Shop portable concentrators', primaryUrl: '/product-category/portable-oxygen-concentrator/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'oxygen-machine-in-dubai': {
		title: 'Oxygen Machine in Dubai',
		keyword: 'oxygen machine in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Oxygen therapy',
				heading: 'Oxygen machines in Dubai: *find the right fit*',
				lead: 'Choosing an oxygen machine in Dubai? Home concentrators, portable concentrators and oxygen cylinders each suit a different routine. Compare them here, then buy or rent from MedHub.',
				primaryLabel: 'Explore oxygen equipment',
				primaryUrl: '/product-category/oxygen-concentrator/',
				secondaryLabel: 'Rent monthly',
				secondaryUrl: RENT_CTA,
				categories: 'buy-oxygen-concentrator,portable-oxygen-concentrator',
			}),
			B.bento({
				eyebrow: 'Three kinds of oxygen equipment',
				heading: 'Which one *fits your day?*',
				layout: 'cards',
				cells: [
					{ h: 'Home concentrator', p: 'Makes oxygen from room air and runs on mains power. Suited to use at home, including overnight.' },
					{ h: 'Portable concentrator', p: 'Battery-powered and small enough to carry, for time away from home and travel.' },
					{ h: 'Oxygen cylinder', p: 'Stores oxygen and needs no power. It must be refilled when empty. A 10-litre cylinder with trolley is available to rent.' },
				],
			}),
			B.links({
				eyebrow: 'Shop by type',
				heading: 'Oxygen *equipment*',
				items: [
					'category:buy-oxygen-concentrator | Home oxygen concentrators',
					'category:portable-oxygen-concentrator | Portable oxygen concentrators',
					'product:oxygen-cylinder-in-dubai | Oxygen cylinder with trolley (rental)',
					'category:oxygen-concentrator-accessories | Accessories',
					'category:medical-equipment-rental | Monthly rental',
					'page:oxygen-concentrator-in-dubai | Oxygen concentrators guide',
				],
			}),
			B.products({ eyebrow: 'Popular now', heading: 'Oxygen *equipment*', categories: 'oxygen-concentrator', limit: 4, linkLabel: 'See all oxygen therapy' }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'Oxygen machine in Dubai: *questions*',
				items: [
					['Which oxygen machine do I need?', 'That depends on the flow rate and hours of use your doctor prescribed, and on whether you need oxygen away from home. We can help you compare options.'],
					['Can I rent an oxygen machine?', 'Yes. Home concentrators, a portable concentrator and a 10-litre oxygen cylinder with trolley are available on monthly rental.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Still deciding?', heading: 'Talk it through *with us.*', text: 'Tell us how and where you use oxygen, and we will suggest the equipment that fits.', primaryLabel: 'Explore oxygen equipment', primaryUrl: '/product-category/oxygen-concentrator/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'sleep-apnea-machine-in-dubai': {
		title: 'Sleep Apnea Machine in Dubai',
		keyword: 'sleep apnea machine in Dubai',
		note: 'SANDBOX uses the clean slug. On live the existing page is /%e2%81%a0sleep-apnea-machine-in-dubai/ (planned 301, needs approval).',
		sections: [
			B.hero({
				eyebrow: 'Sleep care',
				heading: 'Sleep apnea machines in Dubai',
				lead: 'Choosing a sleep apnea machine in Dubai? CPAP, auto CPAP, BiPAP and travel machines, with the masks and accessories that go with them, to buy or rent from MedHub.',
				primaryLabel: 'Shop CPAP machines',
				primaryUrl: '/product-category/auto-cpap-machine/',
				secondaryLabel: 'Shop BiPAP machines',
				secondaryUrl: '/product-category/bipap/',
				categories: 'auto-cpap-machine,cpap,auto-bipap-machine',
			}),
			B.switcher({ eyebrow: 'Compare', heading: 'Sleep apnea machines *by type*', categories: 'auto-cpap-machine,cpap,travel-cpap-machine,bipap,auto-bipap-machine,buy-cpap-bipap-masks' }),
			B.links({
				eyebrow: 'Guides and parts',
				heading: 'Go *deeper*',
				items: [
					'page:cpap-in-dubai | CPAP machines guide',
					'page:bipap-in-dubai | BiPAP machines guide',
					'category:cpap-bipap-accessories | Filters, tubing and humidifiers',
					'category:sleep-support-comfort-solutions | Sleep support and CPAP pillows',
					'brand:resmed | ResMed',
					'brand:philips | Philips Respironics',
				],
			}),
			B.posts({ eyebrow: 'Guides', heading: 'Sleep apnea *guides*', category: 'cpap-and-bipap', count: 3 }),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'Sleep apnea machine in Dubai: *questions*',
				items: [
					['Which sleep apnea machine do I need?', 'Your doctor or sleep specialist decides the type of machine and its settings. We can help you choose a model and mask that match that prescription.'],
					['Can I rent a sleep apnea machine?', 'Yes. An auto CPAP machine with humidifier is available on monthly rental.'],
					['Do you sell masks separately?', 'Yes. Nasal, nasal pillow and full face masks from ResMed and Philips are sold separately.'],
					DELIVERY_QA,
				],
			}),
			B.cta({ eyebrow: 'Need help choosing?', heading: 'Talk to MedHub *before* you buy.', text: 'Bring your prescription and we will help you compare machines and masks.', primaryLabel: 'Shop sleep care', primaryUrl: '/product-category/auto-cpap-machine/', secondaryLabel: 'Contact MedHub', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'devilbiss-service-centre-in-dubai': {
		title: 'DeVilbiss Service Centre in Dubai',
		keyword: 'Devilbiss Service Centre in Dubai',
		sections: [
			B.hero({
				eyebrow: 'Service',
				heading: 'DeVilbiss Service Centre in Dubai',
				lead: 'Repair and maintenance for DeVilbiss respiratory equipment, from CPAP and BiPAP machines to the iGo portable oxygen concentrator, handled by the MedHub team in Deira.',
				primaryLabel: 'Request a service',
				primaryUrl: '/contact-us-medhub/?product=DeVilbiss%20service',
				secondaryLabel: 'Shop DeVilbiss equipment',
				secondaryUrl: '/product-category/oxygen-concentrator/',
				categories: 'buy-oxygen-concentrator,suction-machine',
			}),
			B.bento({
				eyebrow: 'What we service',
				heading: 'DeVilbiss equipment *we look after*',
				layout: 'cards',
				cells: [
					{ h: 'CPAP and BiPAP', list: ['DeVilbiss BLUE CPAP', 'SleepCube CPAP', 'BiPAP and BiPAP ST'] },
					{ h: 'Portable oxygen', list: ['DeVilbiss iGo portable oxygen concentrator'] },
					{ h: 'Masks', list: ['DeVilbiss D100 and D150 CPAP masks'] },
				],
			}),
			B.links({
				eyebrow: 'DeVilbiss in the catalogue',
				heading: 'Buy or *rent*',
				items: [
					'product:devilbiss-compact-525-5-ltr-oxygen-concentrator',
					'product:devilbiss-7325p-ur-vacu-aide-suction-machine',
					'product:devilbiss-compact-1025-10-ltr-oxygen-concentrator-rental | Rent a DeVilbiss 1025 10L',
					'category:suction-machine | Suction machines',
				],
			}),
			B.faq({
				eyebrow: 'FAQ',
				heading: 'DeVilbiss Service Centre in Dubai: *questions*',
				items: [
					['Which DeVilbiss equipment can you service?', 'DeVilbiss BLUE and SleepCube CPAP machines, BiPAP and BiPAP ST machines, the iGo portable oxygen concentrator, and D100 and D150 CPAP masks.'],
					['How do I book a repair?', 'Send an enquiry with the model and a short description of the issue, or bring the equipment to our shop in Port Saeed, Deira.'],
				],
			}),
			B.cta({ eyebrow: 'Equipment not working?', heading: 'Book a DeVilbiss *service.*', text: 'Tell us the model and the problem, and the team will advise on the next step.', primaryLabel: 'Request a service', primaryUrl: '/contact-us-medhub/?product=DeVilbiss%20service', secondaryLabel: 'Visit & contact', secondaryUrl: '/contact-us-medhub/' }),
		],
	},

	'contact-us-medhub': {
		title: 'Contact Us',
		keyword: 'contact MedHub',
		sections: [
			B.hero({
				eyebrow: 'Contact',
				heading: 'Contact *MedHub*',
				lead: 'Questions about equipment, rentals or an order? Send an enquiry or visit our shop in Port Saeed, Deira.',
			}),
			B.contact({
				heading: 'Reach *the team*',
				lead: 'For quotes, include the equipment name and whether you want to buy or rent.',
				formHeading: 'Send an enquiry',
				formLead: 'We will reply using the details you provide.',
			}),
			B.faq({
				eyebrow: 'Before you get in touch',
				heading: 'Quick *answers*',
				items: [
					['Can I cancel an order?', 'Orders can be cancelled before they are processed and shipped. Once an order has shipped, the return and refund policy applies.'],
					['What is your return window?', 'Returns are accepted within 2 days of delivery for items that are damaged, defective or incorrect, and unopened in original packaging. See the refund policy for details.'],
					DELIVERY_QA,
				],
			}),
		],
	},

	faq: {
		title: 'Frequently Asked Questions',
		keyword: 'medical equipment FAQ Dubai',
		note: 'SANDBOX ONLY preview. D5: whether /faq/ becomes a page on live is still open.',
		sections: [
			B.hero({
				eyebrow: 'Help',
				heading: 'Frequently asked *questions*',
				lead: 'Ordering, delivery, returns and rentals at MedHub. Answers are based on our current store policies.',
			}),
			B.faq({
				eyebrow: 'Ordering & payment',
				heading: 'Ordering and *payment*',
				items: [
					['How do I pay?', 'Online by Visa or MasterCard credit or debit card on a secure payment page. Prices are in AED.'],
					['Can I cancel my order?', 'Yes, before it is processed and shipped. Contact us as soon as possible. After shipment, the return and refund policy applies.'],
					['Do you sell medicines?', 'No. MedHub supplies medical equipment and supplies, not medicines.'],
				],
			}),
			B.faq({
				eyebrow: 'Delivery',
				heading: '*Delivery*',
				items: [
					DELIVERY_QA,
					['Do you deliver outside the UAE?', 'Not at the moment. Delivery is available within the United Arab Emirates only.'],
					['When will my order be processed?', 'Within 24 hours of payment confirmation. Orders placed on weekends or public holidays are processed on the next business day.'],
				],
			}),
			B.faq({
				eyebrow: 'Returns & refunds',
				heading: 'Returns and *refunds*',
				items: [
					['What can I return?', 'Items that are damaged, defective or incorrect, reported within 2 days of delivery, and unopened and unused in their original sealed packaging.'],
					['How long do refunds take?', 'Refunds go to the original payment method and can take 10 to 45 working days depending on your card issuer or bank.'],
					['Are delivery charges refunded?', 'No. Shipping and handling charges are non-refundable.'],
				],
			}),
			B.faq({
				eyebrow: 'Rental',
				heading: 'Equipment *rental*',
				items: [
					['What can I rent?', 'Oxygen concentrators (home and portable), an oxygen cylinder with trolley, an auto CPAP machine, electric beds, infusion and syringe pumps, a suction machine, a feeding pump and patient monitors.'],
					['How are rental prices shown?', 'Rental prices are per month and shown on each rental product. Items without a listed price are quoted on request.'],
				],
			}),
			B.cta({ eyebrow: 'Still have a question?', heading: 'Ask the *MedHub team.*', text: 'Send an enquiry and include your order number if it is about an existing order.', primaryLabel: 'Contact MedHub', primaryUrl: '/contact-us-medhub/', secondaryLabel: 'Shop equipment', secondaryUrl: '/shop/' }),
		],
	},
};

/* ------------------------------------------------------------------------- */
/* Category content (keywords owned by existing category URLs)               */
/* Rendered below the product grid of that category.                         */
/* ------------------------------------------------------------------------- */
const catCta = (heading, text) =>
	B.cta({ eyebrow: 'Need advice?', heading, text, primaryLabel: 'Contact MedHub', primaryUrl: '/contact-us-medhub/', secondaryLabel: 'Rent equipment', secondaryUrl: RENT_CTA });

export const categories = {
	'portable-oxygen-concentrator': {
		keyword: 'portable oxygen concentrator Dubai',
		heading: 'Portable Oxygen Concentrators',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'Compare and *explore*', items: ['page:portable-oxygen-machine-in-dubai | How to choose a portable oxygen machine', 'brand:inogen | Inogen', 'category:oxygen-concentrator-accessories | Oxygen accessories', 'product:inogen-g5-portable-oxygen-concentrator-with-16cell-battery-rental | Rent a portable concentrator', 'page:oxygen-concentrator-in-dubai | Home oxygen concentrators'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Portable concentrator *questions*', items: [
				['Pulse dose or continuous flow?', 'Most portable concentrators deliver pulse dose oxygen as you breathe in. Some models also offer continuous flow. Follow your prescription.'],
				['Can I rent one?', 'Yes. A portable oxygen concentrator is available on monthly rental.'],
				DELIVERY_QA,
			] }),
			catCta('Choose a portable unit *with us.*', 'Tell us your flow setting and daily routine, and we will suggest models.'),
		],
	},
	'medical-equipment-rental': {
		keyword: 'medical equipment rental in Dubai',
		heading: 'Medical Equipment Rental in Dubai',
		sections: [
			B.bento({ eyebrow: 'How rental works', heading: 'Rent by *the month*', layout: 'cards', cells: [
				{ h: 'Monthly pricing', p: 'Each rental item shows its price per month. Items without a listed price are quoted on request.' },
				{ h: 'Wide range', p: 'Oxygen, CPAP, electric beds, infusion and syringe pumps, suction, feeding pumps and patient monitors.' },
				{ h: 'Rent or buy', p: 'Several rental models are also sold new, if you decide to keep one for the long term.' },
			] }),
			B.links({ eyebrow: 'Buy instead', heading: 'Shop the *same equipment*', items: ['category:buy-oxygen-concentrator | Home oxygen concentrators', 'category:portable-oxygen-concentrator | Portable oxygen concentrators', 'category:auto-cpap-machine | Auto CPAP machines', 'category:patient-bed | Patient beds', 'category:suction-machine | Suction machines', 'category:enteral-feeding-pump | Feeding pumps'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Rental *questions*', items: [
				['What can I rent?', 'Oxygen concentrators (home and portable), an oxygen cylinder with trolley, an auto CPAP machine, electric beds, infusion and syringe pumps, a suction machine, a feeding pump and patient monitors.'],
				['How is the price shown?', 'Rental prices are per month on each product. For items without a listed price, ask for a quote.'],
			] }),
			catCta('Plan a rental *with us.*', 'Tell us what you need and for how long, and the team will confirm availability.'),
		],
	},
	'hospital-furniture': {
		keyword: 'hospital bed suppliers in Dubai',
		heading: 'Hospital Beds and Medical Furniture',
		sections: [
			B.bento({ eyebrow: 'How to choose', heading: 'Choosing a *hospital bed*', layout: 'cards', cells: [
				{ h: '3 or 5 functions', p: 'A 3-function bed adjusts the back, legs and height. A 5-function bed adds tilt positions. ICU beds offer the widest range.' },
				{ h: 'Mattress included?', p: 'Several beds come with a mattress. Check the product name and description before you order.' },
				{ h: 'Around the bed', p: 'Overbed tables, commode chairs, wheelchairs, patient lifters and pressure mattresses complete a care setup.' },
			] }),
			B.links({ eyebrow: 'Related', heading: 'Beds, rental and *patient care*', items: ['category:patient-bed | Patient beds', 'product:fully-electric-bed-5-function-homecare-bed-rental | Rent an electric bed', 'category:medical-equipment-rental | All rental equipment', 'category:hygiene-and-infection-control-products | Hygiene and bed care'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Hospital bed *questions*', items: [
				['Can I rent a hospital bed?', 'Yes. Electric homecare beds are available on monthly rental.'],
				DELIVERY_QA,
			] }),
			catCta('Set up a care room *with us.*', 'Share the room and care needs, and we will suggest beds and accessories.'),
		],
	},
	'suction-machine': {
		keyword: 'Suction Machine in Dubai',
		heading: 'Suction Machines',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'Suction, airway and *rental*', items: ['product:portable-suction-machine-with-battery-vacuaide-qsu-drive-devilbiss-rental | Rent a portable suction machine', 'category:tracheostomy-care-products-in-dubai | Tracheostomy care', 'category:ventilator-accessories-in-dubai | Ventilator accessories', 'page:devilbiss-service-centre-in-dubai | DeVilbiss service'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Suction machine *questions*', items: [
				['Portable or mains-powered?', 'Portable units have a battery for use away from a socket. Check the capacity and flow of each model against your clinician’s advice.'],
				['Can I rent a suction machine?', 'Yes. A portable DeVilbiss VacuAide suction unit is available on monthly rental, priced on request.'],
				DELIVERY_QA,
			] }),
			catCta('Need a suction unit *quickly?*', 'Tell us whether you need to buy or rent and we will confirm availability.'),
		],
	},
	'enteral-feeding-pump': {
		keyword: 'Enteral Feeding Pump in Dubai',
		heading: 'Enteral Feeding Pumps',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'Sets, nutrition and *rental*', items: ['category:feeding-pump-accessories | Feeding sets and ENFit syringes', 'category:feeding-milk-nutritional-supplement | Feeding milk and supplements', 'category:nasogastric-tubes | Nasogastric tubes', 'product:kangaroo-enteral-feeding-pump-rental | Rent a feeding pump'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Feeding pump *questions*', items: [
				['Which feeding set do I need?', 'Feeding sets are made for specific pumps (for example Kangaroo or Abbott FreeGo). Choose the set listed for your pump model.'],
				['Can I rent a feeding pump?', 'Yes. An enteral feeding pump is available on monthly rental.'],
				DELIVERY_QA,
			] }),
			catCta('Setting up tube feeding *at home?*', 'Tell us the pump and set you use and we will check availability.'),
		],
	},
	'pulse-oximeter': {
		keyword: 'Pulse Oximeter in Dubai',
		heading: 'Pulse Oximeters',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'More *monitoring*', items: ['category:patient-monitor | Patient monitors', 'category:vital-sign-monitor-in-dubai | Vital sign monitors', 'category:health-monitoring-devices | All health monitoring', 'post:pulse-oximeter-dubai | Pulse oximeter guide'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Pulse oximeter *questions*', items: [
				['What does a pulse oximeter measure?', 'Oxygen saturation (SpO2) and pulse rate. Clinical models such as the Masimo Rad-97 add further parameters listed on the product page.'],
				DELIVERY_QA,
			] }),
			catCta('Choosing a monitor for *a clinic?*', 'Tell us the parameters you need and we will suggest models.'),
		],
	},
	'patient-monitor': {
		keyword: 'Patient Monitor in Dubai',
		heading: 'Patient Monitors',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'Monitoring and *rental*', items: ['category:vital-sign-monitor-in-dubai | Vital sign monitors', 'category:pulse-oximeter | Pulse oximeters', 'category:electrocardiogram-machine | ECG machines', 'product:yk-8000c-patient-bedside-monitor-rental | Rent a bedside monitor'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Patient monitor *questions*', items: [
				['Can I rent a patient monitor?', 'Yes. A bedside patient monitor and a vital sign monitor are available on monthly rental.'],
				DELIVERY_QA,
			] }),
			catCta('Monitoring for home or *clinic?*', 'Tell us where the monitor will be used and which readings you need.'),
		],
	},
	'medical-disposables': {
		keyword: 'Medical disposables in Dubai',
		heading: 'Medical Disposables and Supplies',
		sections: [
			B.links({ eyebrow: 'Shop supplies', heading: 'Everyday *medical supplies*', items: ['category:hygiene-and-infection-control-products | Hygiene and infection control', 'category:wound-care | Wound care dressings', 'category:tracheostomy-care-products-in-dubai | Tracheostomy care', 'category:feeding-pump-accessories | Feeding sets and syringes', 'category:nasogastric-tubes | Nasogastric tubes'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Supplies *questions*', items: [
				['Can I return opened supplies?', 'Returns are accepted only for items that are damaged, defective or incorrect and still unopened in their original sealed packaging, reported within 2 days of delivery.'],
				DELIVERY_QA,
			] }),
			catCta('Ordering for a clinic or *care home?*', 'Send your list and quantities and the team will confirm availability.'),
		],
	},
	'buy-cpap-bipap-masks': {
		keyword: 'CPAP/BiPAP Masks in Dubai',
		heading: 'CPAP and BiPAP Masks',
		sections: [
			B.bento({ eyebrow: 'How to choose', heading: 'Three *mask styles*', layout: 'cards', cells: [
				{ h: 'Nasal', p: 'Covers the nose. A common first choice for people who breathe through the nose while sleeping.' },
				{ h: 'Nasal pillow', p: 'Small cushions at the nostrils. Minimal contact, popular with people who move a lot at night.' },
				{ h: 'Full face', p: 'Covers the nose and mouth. Often chosen by people who breathe through the mouth.' },
			] }),
			B.links({ eyebrow: 'Related', heading: 'Machines and *parts*', items: ['category:cpap-bipap-accessories | Filters, tubing and humidifiers', 'brand:resmed | ResMed', 'page:cpap-in-dubai | CPAP machines', 'page:bipap-in-dubai | BiPAP machines', 'post:cpap-masks-dubai | CPAP mask guide'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Mask *questions*', items: [
				['Will a mask fit my machine?', 'Most CPAP and BiPAP masks use a standard tubing connection. Check the product description, or ask us with your machine model.'],
				['Can I return a mask that does not fit?', 'Returns are accepted only for items that are damaged, defective or incorrect and unopened in original packaging, within 2 days of delivery.'],
				DELIVERY_QA,
			] }),
			catCta('Not sure which *mask?*', 'Tell us your machine and how you sleep, and we will suggest styles and sizes.'),
		],
	},
	'cpap-bipap-accessories': {
		keyword: 'CPAP/BiPAP Accessories in Dubai',
		heading: 'CPAP and BiPAP Accessories',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'Masks and *machines*', items: ['category:buy-cpap-bipap-masks | CPAP and BiPAP masks', 'category:auto-cpap-machine | Auto CPAP machines', 'category:bipap | BiPAP machines', 'category:sleep-support-comfort-solutions | CPAP pillows and sleep support', 'brand:resmed | ResMed'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Accessory *questions*', items: [
				['Which filters fit my machine?', 'Filters are model-specific (for example ResMed AirSense S9/S10 or AirMini). Check the product name, or ask us with your machine model.'],
				DELIVERY_QA,
			] }),
			catCta('Need the right *part?*', 'Send your machine model and we will check compatible accessories.'),
		],
	},
	'travel-cpap-machine': {
		keyword: 'Travel CPAP Machine in Dubai',
		heading: 'Travel and Portable CPAP Machines',
		sections: [
			B.links({ eyebrow: 'Related', heading: 'For your *trip*', items: ['category:cpap-bipap-accessories | Travel filters and tubing', 'category:buy-cpap-bipap-masks | Masks', 'page:cpap-in-dubai | CPAP machines guide', 'post:travel-cpap-machine-in-dubai-best-portable-cpap-devices-for-sleep-apnea-on-the-go | Travel CPAP guide'] }),
			B.faq({ eyebrow: 'FAQ', heading: 'Travel CPAP *questions*', items: [
				['Can I use a travel CPAP on a plane?', 'Airlines set their own rules for using CPAP machines on board. Check with your airline before you fly.'],
				['Is a travel CPAP different from a home CPAP?', 'It is smaller and lighter, usually with fewer built-in features such as humidification. Settings still follow your prescription.'],
				DELIVERY_QA,
			] }),
			catCta('Travelling *soon?*', 'Tell us where you are going and for how long, and we will suggest a setup.'),
		],
	},
};
