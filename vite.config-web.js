import { defineConfig, splitVendorChunkPlugin } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from "@rollup/plugin-inject"

export default defineConfig({
    plugins: [
        inject({   // => that should be first under plugins array
            $: 'jquery',
            jQuery: 'jquery',
        }),
        splitVendorChunkPlugin(),
        laravel({
            input: [
                'resources/sass/web.scss',
                'resources/js/web.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build-web',
      }
});
