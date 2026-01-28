import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

// Check if local SSL certificates exist (for PWA testing)
const certPath = path.resolve(__dirname, 'localhost.pem');
const keyPath = path.resolve(__dirname, 'localhost-key.pem');
const hasSSL = fs.existsSync(certPath) && fs.existsSync(keyPath);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Server Configuration
    |--------------------------------------------------------------------------
    | Enable HTTPS for local PWA testing. Service workers require HTTPS
    | (except on localhost, but HTTPS provides better testing fidelity).
    |
    | To generate certificates, run:
    | brew install mkcert  (macOS)
    | mkcert -install
    | mkcert localhost
    |
    */
    server: {
        // Enable HTTPS if certificates exist
        ...(hasSSL && {
            https: {
                key: fs.readFileSync(keyPath),
                cert: fs.readFileSync(certPath),
            },
        }),

        // Allow access from mobile devices on same network
        host: '0.0.0.0',

        // Use a consistent port
        port: 5173,

        // CORS for PWA service worker
        cors: true,

        // Hot Module Replacement
        hmr: {
            host: 'localhost',
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Build Configuration
    |--------------------------------------------------------------------------
    */
    build: {
        // Generate source maps for debugging
        sourcemap: true,

        // Optimize chunk splitting for better caching
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate vendor chunks for better caching
                    'alpine': ['alpinejs'],
                },
            },
        },

        // Target modern browsers (better for PWA)
        target: 'es2020',

        // CSS code splitting
        cssCodeSplit: true,
    },

    /*
    |--------------------------------------------------------------------------
    | Optimization
    |--------------------------------------------------------------------------
    */
    optimizeDeps: {
        include: ['alpinejs'],
    },

    /*
    |--------------------------------------------------------------------------
    | CSS Configuration
    |--------------------------------------------------------------------------
    */
    css: {
        devSourcemap: true,
    },
});