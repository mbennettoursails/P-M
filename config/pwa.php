<?php

/**
 * PWA Configuration for North Tokyo COOP Hub
 * 
 * Icons generated from PWABuilder Image Generator
 * 
 * After editing this file, run:
 * php artisan erag:pwa-update-manifest
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Install Button
    |--------------------------------------------------------------------------
    | Show or hide the PWA install button globally.
    | Use @PwaInstallButton directive in your Blade views.
    */
    'install-button' => true,

    /*
    |--------------------------------------------------------------------------
    | Manifest
    |--------------------------------------------------------------------------
    | Configure your Progressive Web App manifest here.
    */
    'manifest' => [
        // Full application name (shown in install prompt)
        'name' => '北東京CO-OP Hub',
        
        // Short name (shown under app icon, max 12 characters)
        'short_name' => 'CO-OP Hub',
        
        // App description
        'description' => '北東京生活クラブ生活協同組合連合会のメンバー向けコミュニティプラットフォーム。ニュース、イベント、相互扶助、意思決定をサポートします。',
        
        // Background color (splash screen)
        'background_color' => '#ffffff',
        
        // Display mode: fullscreen, standalone, minimal-ui, browser
        // 'standalone' recommended for app-like experience
        'display' => 'standalone',
        
        // Theme color (address bar, task switcher)
        'theme_color' => '#16a34a',
        
        // Orientation: portrait-primary, landscape-primary, any
        'orientation' => 'portrait-primary',
        
        // Starting URL when app is launched
        'start_url' => '/',
        
        // Navigation scope (pages outside this open in browser)
        'scope' => '/',
        
        // Language
        'lang' => 'ja',
        
        // Text direction
        'dir' => 'ltr',
        
        // App categories for app stores
        'categories' => ['social', 'productivity', 'lifestyle'],
        
        /*
        |--------------------------------------------------------------------------
        | Icons - Using PWABuilder Generated Files
        |--------------------------------------------------------------------------
        | These paths match the PWABuilder output structure.
        | Place icons in: public/images/icons/
        |
        | IMPORTANT: PWABuilder requires at minimum:
        | - 192x192 icon with purpose: any
        | - 512x512 icon with purpose: any
        | - 512x512 icon with purpose: maskable (for Android adaptive icons)
        */
        'icons' => [
            // ============================================
            // Android Icons (from icons/android/)
            // ============================================
            [
                'src' => 'images/icons/android/android-launchericon-48-48.png',
                'sizes' => '48x48',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-72-72.png',
                'sizes' => '72x72',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-96-96.png',
                'sizes' => '96x96',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-144-144.png',
                'sizes' => '144x144',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-192-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-512-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            
            // ============================================
            // iOS Icons (from icons/ios/)
            // These are used for apple-touch-icon
            // ============================================
            [
                'src' => 'images/icons/ios/152.png',
                'sizes' => '152x152',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/ios/167.png',
                'sizes' => '167x167',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => 'images/icons/ios/180.png',
                'sizes' => '180x180',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            
            // ============================================
            // Maskable Icons (for Android adaptive icons)
            // ============================================
            // Note: PWABuilder generates icons that work as maskable.
            // The Android 192 and 512 icons can serve double duty.
            // If you have specifically designed maskable icons with
            // safe zone padding, create separate files.
            [
                'src' => 'images/icons/android/android-launchericon-192-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => 'images/icons/android/android-launchericon-512-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Shortcuts
        |--------------------------------------------------------------------------
        | Quick access shortcuts shown when long-pressing the app icon.
        | Uses iOS icons for shortcut images.
        */
        'shortcuts' => [
            [
                'name' => 'ニュース',
                'short_name' => 'News',
                'description' => '最新のニュースを見る',
                'url' => '/news',
                'icons' => [
                    [
                        'src' => 'images/icons/ios/192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    ],
                ],
            ],
            [
                'name' => 'イベント',
                'short_name' => 'Events',
                'description' => 'イベントカレンダーを開く',
                'url' => '/events',
                'icons' => [
                    [
                        'src' => 'images/icons/ios/192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    ],
                ],
            ],
            [
                'name' => 'コミュニティ',
                'short_name' => 'Community',
                'description' => '相互扶助掲示板',
                'url' => '/community',
                'icons' => [
                    [
                        'src' => 'images/icons/ios/192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    ],
                ],
            ],
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Screenshots
        |--------------------------------------------------------------------------
        | Screenshots for rich install prompt (Android Chrome).
        | You'll need to create these yourself - take screenshots of your app.
        | Recommended: 1080x1920 for mobile, 1920x1080 for desktop
        */
        'screenshots' => [
            [
                'src' => 'images/screenshots/screenshot-mobile-1.png',
                'sizes' => '1080x1920',
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'ダッシュボード画面',
            ],
            [
                'src' => 'images/screenshots/screenshot-desktop-1.png',
                'sizes' => '1920x1080',
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'デスクトップダッシュボード',
            ],
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Related Applications
        |--------------------------------------------------------------------------
        | Leave empty for pure PWA. Set prefer_related_applications to false.
        */
        'related_applications' => [],
        'prefer_related_applications' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    | When true, shows console.log messages in browser for debugging.
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire App
    |--------------------------------------------------------------------------
    | Set to true if your app uses Livewire for better compatibility.
    */
    'livewire-app' => true,
];