<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('format_gdrive_image_url')) {
    /**
     * Mengubah URL share Google Drive menjadi URL gambar langsung (direct embed)
     * melalui CDN Google (lh3.googleusercontent.com/d/{id}).
     * Jika bukan link Google Drive, mengembalikan URL asli tanpa modifikasi.
     *
     * @param string|null $url
     * @return string|null
     */
    function format_gdrive_image_url(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Jika sudah berupa link direct Google CDN
        if (strpos($url, 'lh3.googleusercontent.com/d/') !== false) {
            return $url;
        }

        // Pattern 1: drive.google.com/file/d/{id}
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#i', $url, $m)) {
            return 'https://lh3.googleusercontent.com/d/' . $m[1];
        }

        // Pattern 2: parameter ?id={id} atau &id={id} pada domain google.com
        if (preg_match('#(?:drive|docs)\.google\.com/.*[?&]id=([a-zA-Z0-9_-]+)#i', $url, $m)) {
            return 'https://lh3.googleusercontent.com/d/' . $m[1];
        }

        // Pattern 3: drive.google.com/open?id={id}
        if (preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#i', $url, $m)) {
            return 'https://lh3.googleusercontent.com/d/' . $m[1];
        }

        return $url;
    }
}

