import { defineConfig, splitVendorChunkPlugin } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from "@rollup/plugin-inject"

export default defineConfig({
    plugins: [
        inject({   // => that should be first under plugins array
            $: 'jquery',
            jQuery: 'jquery',
            moment: 'moment',
        }),
        splitVendorChunkPlugin(),
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // build: {
    //     rollupOptions: {
    //       output: {
    //         entryFileNames: `assets/js/[name].js`,
    //         chunkFileNames: `assets/js/[name].js`,
    //         assetFileNames: `assets/css/[name].[ext]`
    //       }
    //     }
    //   }
});
