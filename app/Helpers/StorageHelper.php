<?php

if (!function_exists('storage_url')) {
    /**
     * Generate URL untuk file storage
     * Menggunakan route dengan auth untuk keamanan
     */
    function storage_url($path)
    {
        // Pastikan path tidak kosong
        if (empty($path)) {
            return '';
        }
        
        // Gunakan route dengan auth untuk serve file
        return route('storage.serve', $path);
    }
}

