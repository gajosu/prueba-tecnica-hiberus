import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  root: 'assets',
  base: '/build/',
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
    manifest: true,
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
    hmr: {
      host: 'localhost',
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './assets'),
    },
  },
});

