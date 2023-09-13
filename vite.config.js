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
                'resources/sass/web.scss',
                'resources/sass/tinymce.scss',
                'resources/js/app.js',
                'resources/js/web.js',
                'resources/js/tinymce.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
          output: {
            manualChunks(id) {
              // creating a chunk to @open-ish deps. Reducing the vendor chunk size
              if (id.includes('admin-lte') || id.includes('bootstrap')) {
                return 'admin-lte';
              }
              // creating a chunk to react routes deps. Reducing the vendor chunk size
              if (
                id.includes('tinymce')
              ) {
                return 'tinymce';
              }
              if (
                id.includes('datatables')
              ) {
                return 'datatables';
              }
            },
          },
        },
      }
});
