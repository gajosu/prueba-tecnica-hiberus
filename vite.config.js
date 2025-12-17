import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import symfonyPlugin from 'vite-plugin-symfony';
import { resolve } from 'path';

export default defineConfig({
  plugins: [
    react(),
    symfonyPlugin({
      refresh: true,
      stimulus: false,
    }),
  ],
  root: '.',
  base: '/build/',
  build: {
    manifest: true,
    outDir: './public/build',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: './assets/app.jsx',
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://localhost:5173',
    hmr: {
      host: 'localhost',
      protocol: 'ws',
      port: 5173,
    },
    watch: {
      usePolling: true,
      interval: 100,
      ignored: ['**/node_modules/**', '**/vendor/**', '**/var/**', '**/public/build/**'],
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './assets'),
    },
  },
});
