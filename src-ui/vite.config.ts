/* eslint-disable import/no-extraneous-dependencies */
/// <reference types="vite/client" />

import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
// import { visualizer } from 'rollup-plugin-visualizer';

const alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
function base62(n: number) {
    if (n === 0) {
        return alphabet.charAt(0);
    }
    let i = n;
    let sb = '';
    while (i > 0) {
        const rem = i % 62;
        sb = alphabet.charAt(rem) + sb;
        i = Math.floor(i / 62);
    }
    return sb;
}

function scopedNameGenerator() {
    const counterMap: Record<string, number> = {};
    let counter = 1;
    return (name: string, filename: string) => {
        const key = `${filename}_${name}`;
        let c: number;
        if (counterMap[key]) {
            c = counterMap[key];
        } else {
            counterMap[key] = counter;
            c = counter;
            counter += 1;
        }
        return `wpswlr-${base62(c)}`;
    };
}

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
    plugins: [
        react(),
        // visualizer({ emitFile: true, filename: 'stats.html' })
    ],
    define: {
        'process.env.NODE_ENV': `"${mode}"`,
    },
    css: {
        modules: {
            generateScopedName: mode === 'development' ? undefined : scopedNameGenerator(),
        },
    },
    build: {
        target: 'es2020',
        outDir: resolve(__dirname, '../assets/admin'),
        emptyOutDir: true,
        lib: {
            entry: resolve(__dirname, 'src/main.tsx'),
            fileName: 'app',
            name: 'wpswlr',
            formats: ['iife'],
        },
        minify: mode !== 'development',
    },
}));
