import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  server: {
    host: 'laravel-brive.test',
    https: {
      key: './laravel-brive.test-key.pem',
      cert: './laravel-brive.test.pem',
    },
    hmr: {
      host: 'laravel-brive.test',
      protocol: 'wss',
    },
    watch: {
      usePolling: true,
    },
  },
  build: {
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      external: [],
      output: {
        manualChunks: {}, // Removed lodash from manualChunks
      },
    },
  },
  plugins: [
    laravel({
      input: [
        'resources/js/app.js'
          ],
      ssr: ['resources/js/ssr.js'],
      refresh: true,
    }),
    vue(),
  ],
});
