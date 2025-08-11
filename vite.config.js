import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
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
  logLevel: 'error',
  clearScreen: false,
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
        'resources/js/app.js',
        'resources/js/Pages/Index/Welcome.vue' // Explicitly include Welcome.vue
      ],
      ssr: ['resources/js/ssr.js'],
      refresh: true,
    }),
    vue(),
  ],
});
