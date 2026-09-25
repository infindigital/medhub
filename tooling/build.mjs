/**
 * MedHub theme build: bundles + minifies CSS and JS entry points with esbuild.
 *
 *   npm run build   one-off production build
 *   npm run watch   rebuild on change (local development)
 *
 * Entry points = every top-level file in assets/src/css and assets/src/js.
 * Partials live in sub-folders and are pulled in with @import / import.
 * Output goes to assets/dist/ and is committed, so the server never builds.
 */
import * as esbuild from 'esbuild';
import { readdirSync, rmSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', 'medhub-theme', 'assets');
const watch = process.argv.includes('--watch');

const entries = (dir, ext) =>
	readdirSync(join(root, 'src', dir), { withFileTypes: true })
		.filter((f) => f.isFile() && f.name.endsWith(ext))
		.map((f) => join(root, 'src', dir, f.name));

const shared = {
	bundle: true,
	minify: !watch,
	sourcemap: watch ? 'inline' : false,
	logLevel: 'info',
	legalComments: 'none',
};

const configs = [
	{
		...shared,
		entryPoints: entries('css', '.css'),
		outdir: join(root, 'dist', 'css'),
		// Fonts/icons are referenced by URL and copied as-is, never inlined.
		external: ['../../fonts/*', '../../icons/*', '../../images/*', '../fonts/*', '../icons/*', '../images/*'],
		target: ['chrome111', 'safari16.4', 'firefox115'],
	},
	{
		...shared,
		entryPoints: entries('js', '.js'),
		outdir: join(root, 'dist', 'js'),
		format: 'esm',
		target: ['es2020'],
	},
];

if (watch) {
	for (const config of configs) {
		const ctx = await esbuild.context(config);
		await ctx.watch();
	}
	console.log('Watching medhub-theme/assets/src …');
} else {
	rmSync(join(root, 'dist'), { recursive: true, force: true });
	await Promise.all(configs.map((config) => esbuild.build(config)));
}
