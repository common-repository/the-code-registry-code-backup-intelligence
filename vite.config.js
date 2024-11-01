import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  css: {
    postcss: {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
      ],
    },
  },
  build: {
    outDir: 'admin',
    emptyOutDir: false,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'admin/js/src/main.js'),
        styles: path.resolve(__dirname, 'admin/css/index.css'),
      },
      output: {
        entryFileNames: 'js/dist/[name].js',
        chunkFileNames: 'js/dist/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'styles.css') {
            return 'css/dist/app.css'
          }
          return 'js/dist/[name][extname]'
        }
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './admin/js/src')
    }
  }
})