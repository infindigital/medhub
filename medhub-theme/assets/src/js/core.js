/**
 * core.js – the only front-end script (ES module, deferred by default).
 * Every module is progressive enhancement: the markup works without it.
 */
import { initHeader } from './modules/header.js';
import { initNav } from './modules/nav.js';
import { initDialogs } from './modules/dialogs.js';
import { initSearch } from './modules/search.js';
import { initTabs } from './modules/tabs.js';
import { initRails } from './modules/rail.js';
import { initReveal } from './modules/reveal.js';
import { initShop } from './modules/shop.js';
import { initProduct } from './modules/product.js';
import { initDevForm } from './modules/devform.js';

document.documentElement.classList.add('js');

initHeader();
initNav();
initDialogs();
initSearch();
initTabs();
initRails();
initReveal();
initShop();
initProduct();
initDevForm();
