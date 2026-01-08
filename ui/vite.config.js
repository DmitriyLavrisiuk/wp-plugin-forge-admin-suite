import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
var __filename = fileURLToPath(import.meta.url);
var __dirname = path.dirname(__filename);
export default defineConfig({
    plugins: [react()],
    base: './',
    build: {
        outDir: 'dist',
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: {
                admin: path.resolve(__dirname, 'src/main.tsx'),
                frontend: path.resolve(__dirname, 'src/frontend.ts'),
            },
        },
    },
});
