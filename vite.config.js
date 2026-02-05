import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  // 👇 এই অংশটুকু আসল ম্যাজিক (CORS বাইপাস)
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000', // আপনার লারাভেল সার্ভার
        changeOrigin: true,
        secure: false,
      }
    }
  }
})