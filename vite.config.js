import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/login.css',
                'resources/css/catalogo.css',
                'resources/css/dashboard.css',
                'resources/css/produccion.css',
                'resources/css/control.css',
                'resources/css/eventos.css',
                'resources/css/productos.css',
                'resources/css/presentaciones.css',
                'resources/css/usuarios.css',
                'resources/css/reportes-show.css',
                'resources/css/comercial.css',
                'resources/js/app.js',
                'resources/js/catalogo.js',
                'resources/js/dashboard.js',
                'resources/js/produccion.js',
                'resources/js/control.js',
                'resources/js/productos.js',
                'resources/js/comercial.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
