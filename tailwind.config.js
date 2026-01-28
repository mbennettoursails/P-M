import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            /*
            |------------------------------------------------------------------
            | Font Families - Japanese Support
            |------------------------------------------------------------------
            */
            fontFamily: {
                sans: ['Noto Sans JP', 'Figtree', ...defaultTheme.fontFamily.sans],
                // Use for headings if you want variety
                display: ['Figtree', 'Noto Sans JP', ...defaultTheme.fontFamily.sans],
            },

            /*
            |------------------------------------------------------------------
            | Brand Colors - COOP Hub Green Theme
            |------------------------------------------------------------------
            */
            colors: {
                // Primary brand color (green)
                primary: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a', // Main brand color
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                    950: '#052e16',
                },
                // Alias for convenience
                coop: {
                    light: '#dcfce7',
                    DEFAULT: '#16a34a',
                    dark: '#15803d',
                },
            },

            /*
            |------------------------------------------------------------------
            | Spacing - Touch-Friendly Sizes
            |------------------------------------------------------------------
            | Minimum touch target: 48px (Apple HIG) / 48dp (Material Design)
            */
            spacing: {
                // Touch-friendly minimum sizes
                'touch': '48px',      // Minimum touch target
                'touch-lg': '56px',   // Comfortable touch target
                // Safe area utilities (for iOS notch)
                'safe-top': 'env(safe-area-inset-top)',
                'safe-bottom': 'env(safe-area-inset-bottom)',
                'safe-left': 'env(safe-area-inset-left)',
                'safe-right': 'env(safe-area-inset-right)',
            },

            /*
            |------------------------------------------------------------------
            | Min Height/Width for Touch Targets
            |------------------------------------------------------------------
            */
            minHeight: {
                'touch': '48px',
                'touch-lg': '56px',
            },
            minWidth: {
                'touch': '48px',
                'touch-lg': '56px',
            },

            /*
            |------------------------------------------------------------------
            | Font Sizes - Mobile Optimized
            |------------------------------------------------------------------
            | Slightly larger base for readability on mobile
            */
            fontSize: {
                // Mobile-friendly sizes
                'xs': ['0.75rem', { lineHeight: '1rem' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                'base': ['1rem', { lineHeight: '1.625rem' }],      // Increased line height for Japanese
                'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                'xl': ['1.25rem', { lineHeight: '1.875rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['1.875rem', { lineHeight: '2.375rem' }],
                '4xl': ['2.25rem', { lineHeight: '2.75rem' }],
                // Japanese optimized (tighter for kanji)
                'jp-sm': ['0.875rem', { lineHeight: '1.5rem' }],
                'jp-base': ['1rem', { lineHeight: '1.75rem' }],
                'jp-lg': ['1.125rem', { lineHeight: '1.875rem' }],
            },

            /*
            |------------------------------------------------------------------
            | Border Radius - Consistent rounded corners
            |------------------------------------------------------------------
            */
            borderRadius: {
                'card': '0.75rem',    // 12px - Standard card radius
                'button': '0.5rem',   // 8px - Button radius
                'input': '0.5rem',    // 8px - Input radius
                'modal': '1rem',      // 16px - Modal radius
                'full-safe': '9999px', // Pill shape
            },

            /*
            |------------------------------------------------------------------
            | Box Shadows - Subtle elevation
            |------------------------------------------------------------------
            */
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
                'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                'bottom-nav': '0 -1px 3px 0 rgba(0, 0, 0, 0.1)',
                'top-nav': '0 1px 3px 0 rgba(0, 0, 0, 0.1)',
            },

            /*
            |------------------------------------------------------------------
            | Z-Index Scale
            |------------------------------------------------------------------
            */
            zIndex: {
                'bottom-nav': '40',
                'header': '40',
                'sidebar': '45',
                'modal': '50',
                'toast': '60',
                'tooltip': '70',
            },

            /*
            |------------------------------------------------------------------
            | Animations - Smooth interactions
            |------------------------------------------------------------------
            */
            animation: {
                'fade-in': 'fadeIn 0.2s ease-out',
                'fade-out': 'fadeOut 0.2s ease-in',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'slide-in-right': 'slideInRight 0.3s ease-out',
                'slide-in-left': 'slideInLeft 0.3s ease-out',
                'bounce-subtle': 'bounceSubtle 0.5s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'spin-slow': 'spin 2s linear infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeOut: {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideInRight: {
                    '0%': { transform: 'translateX(100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                slideInLeft: {
                    '0%': { transform: 'translateX(-100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                bounceSubtle: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-5px)' },
                },
            },

            /*
            |------------------------------------------------------------------
            | Transitions
            |------------------------------------------------------------------
            */
            transitionDuration: {
                '250': '250ms',
                '350': '350ms',
            },

            /*
            |------------------------------------------------------------------
            | Screens - Mobile-First Breakpoints
            |------------------------------------------------------------------
            | Default Tailwind breakpoints work well, but we add some custom ones
            */
            screens: {
                'xs': '375px',        // Small phones (iPhone SE)
                // 'sm': '640px',     // Default
                // 'md': '768px',     // Default  
                // 'lg': '1024px',    // Default
                // 'xl': '1280px',    // Default
                // '2xl': '1536px',   // Default
                'tall': { 'raw': '(min-height: 800px)' },  // For tall screens
                'short': { 'raw': '(max-height: 600px)' }, // For short screens
                'touch': { 'raw': '(hover: none)' },       // Touch devices
                'mouse': { 'raw': '(hover: hover)' },      // Mouse/trackpad devices
            },

            /*
            |------------------------------------------------------------------
            | Aspect Ratios
            |------------------------------------------------------------------
            */
            aspectRatio: {
                'card': '3 / 2',
                'thumbnail': '4 / 3',
                'banner': '16 / 9',
                'square': '1 / 1',
            },
        },
    },

    plugins: [
        forms,
        typography,
        
        /*
        |----------------------------------------------------------------------
        | Custom Plugin: Mobile-First Utilities
        |----------------------------------------------------------------------
        */
        function({ addUtilities, addComponents, theme }) {
            // Safe Area Utilities
            addUtilities({
                '.safe-top': {
                    paddingTop: 'env(safe-area-inset-top)',
                },
                '.safe-bottom': {
                    paddingBottom: 'env(safe-area-inset-bottom)',
                },
                '.safe-left': {
                    paddingLeft: 'env(safe-area-inset-left)',
                },
                '.safe-right': {
                    paddingRight: 'env(safe-area-inset-right)',
                },
                '.safe-x': {
                    paddingLeft: 'env(safe-area-inset-left)',
                    paddingRight: 'env(safe-area-inset-right)',
                },
                '.safe-y': {
                    paddingTop: 'env(safe-area-inset-top)',
                    paddingBottom: 'env(safe-area-inset-bottom)',
                },
                '.safe-all': {
                    paddingTop: 'env(safe-area-inset-top)',
                    paddingRight: 'env(safe-area-inset-right)',
                    paddingBottom: 'env(safe-area-inset-bottom)',
                    paddingLeft: 'env(safe-area-inset-left)',
                },
            });

            // Touch Target Utilities
            addUtilities({
                '.touch-target': {
                    minHeight: '48px',
                    minWidth: '48px',
                },
                '.touch-target-lg': {
                    minHeight: '56px',
                    minWidth: '56px',
                },
            });

            // Japanese Text Utilities
            addUtilities({
                '.text-japanese': {
                    fontFamily: "'Noto Sans JP', 'Figtree', sans-serif",
                    wordBreak: 'keep-all',
                    overflowWrap: 'break-word',
                },
                '.break-japanese': {
                    wordBreak: 'keep-all',
                    overflowWrap: 'break-word',
                },
            });

            // Tap Highlight Removal (for better PWA feel)
            addUtilities({
                '.tap-transparent': {
                    '-webkit-tap-highlight-color': 'transparent',
                },
            });

            // Prevent Text Selection (for UI elements)
            addUtilities({
                '.select-none-touch': {
                    '-webkit-user-select': 'none',
                    '-webkit-touch-callout': 'none',
                    userSelect: 'none',
                },
            });

            // Smooth Scrolling Container
            addUtilities({
                '.scroll-smooth-touch': {
                    '-webkit-overflow-scrolling': 'touch',
                    scrollBehavior: 'smooth',
                },
            });

            // Hide Scrollbar but Keep Functionality
            addUtilities({
                '.scrollbar-hide': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none',
                    },
                },
            });

            // Card Component
            addComponents({
                '.card': {
                    backgroundColor: theme('colors.white'),
                    borderRadius: theme('borderRadius.card'),
                    boxShadow: theme('boxShadow.card'),
                    overflow: 'hidden',
                    '@media (prefers-color-scheme: dark)': {
                        backgroundColor: theme('colors.gray.800'),
                    },
                },
                '.card-hover': {
                    transition: 'box-shadow 0.2s ease, transform 0.2s ease',
                    '&:hover': {
                        boxShadow: theme('boxShadow.card-hover'),
                        transform: 'translateY(-1px)',
                    },
                },
            });

            // Button Base Components
            addComponents({
                '.btn': {
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    minHeight: '48px',
                    paddingLeft: theme('spacing.4'),
                    paddingRight: theme('spacing.4'),
                    fontSize: theme('fontSize.sm')[0],
                    fontWeight: theme('fontWeight.medium'),
                    borderRadius: theme('borderRadius.button'),
                    transition: 'all 0.2s ease',
                    '-webkit-tap-highlight-color': 'transparent',
                    '&:focus': {
                        outline: 'none',
                        ringWidth: '2px',
                        ringColor: theme('colors.primary.500'),
                        ringOffsetWidth: '2px',
                    },
                },
                '.btn-primary': {
                    backgroundColor: theme('colors.primary.600'),
                    color: theme('colors.white'),
                    '&:hover': {
                        backgroundColor: theme('colors.primary.700'),
                    },
                    '&:active': {
                        backgroundColor: theme('colors.primary.800'),
                    },
                },
                '.btn-secondary': {
                    backgroundColor: theme('colors.gray.100'),
                    color: theme('colors.gray.900'),
                    '&:hover': {
                        backgroundColor: theme('colors.gray.200'),
                    },
                    '@media (prefers-color-scheme: dark)': {
                        backgroundColor: theme('colors.gray.700'),
                        color: theme('colors.gray.100'),
                        '&:hover': {
                            backgroundColor: theme('colors.gray.600'),
                        },
                    },
                },
                '.btn-outline': {
                    backgroundColor: 'transparent',
                    borderWidth: '1px',
                    borderColor: theme('colors.primary.600'),
                    color: theme('colors.primary.600'),
                    '&:hover': {
                        backgroundColor: theme('colors.primary.50'),
                    },
                },
            });

            // Input Base Component
            addComponents({
                '.input': {
                    display: 'block',
                    width: '100%',
                    minHeight: '48px',
                    paddingLeft: theme('spacing.3'),
                    paddingRight: theme('spacing.3'),
                    fontSize: theme('fontSize.base')[0],
                    borderRadius: theme('borderRadius.input'),
                    borderWidth: '1px',
                    borderColor: theme('colors.gray.300'),
                    backgroundColor: theme('colors.white'),
                    '&:focus': {
                        outline: 'none',
                        ringWidth: '2px',
                        ringColor: theme('colors.primary.500'),
                        borderColor: theme('colors.primary.500'),
                    },
                    '@media (prefers-color-scheme: dark)': {
                        backgroundColor: theme('colors.gray.700'),
                        borderColor: theme('colors.gray.600'),
                        color: theme('colors.gray.100'),
                    },
                },
            });
        },
    ],
};