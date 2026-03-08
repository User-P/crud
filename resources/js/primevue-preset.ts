import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'

/**
 * CosmosPreset — PrimeVue theme aligned with the Cosmos design system.
 * Primary palette: violet (matches CSS variables --th-item-active-color, etc.)
 *
 * Component tokens must use the section shape expected by @primeuix/themes:
 * e.g. button: { root: { ... } }, inputtext: { root: { ... } }
 */
const CosmosPreset = definePreset(Aura, {
    primitive: {
        violet: {
            50:  '#f5f3ff',
            100: '#ede9fe',
            200: '#ddd6fe',
            300: '#c4b5fd',
            400: '#a78bfa',
            500: '#8b5cf6',
            600: '#7c3aed',
            700: '#6d28d9',
            800: '#5b21b6',
            900: '#4c1d95',
            950: '#2e1065',
        },
        borderRadius: {
            none:  '0',
            xs:    '0.375rem',
            sm:    '0.5rem',
            md:    '0.625rem',
            lg:    '0.75rem',
            xl:    '1rem',
            '2xl': '1.25rem',
        },
    },

    semantic: {
        primary: {
            50:  '{violet.50}',
            100: '{violet.100}',
            200: '{violet.200}',
            300: '{violet.300}',
            400: '{violet.400}',
            500: '{violet.500}',
            600: '{violet.600}',
            700: '{violet.700}',
            800: '{violet.800}',
            900: '{violet.900}',
            950: '{violet.950}',
        },

        colorScheme: {
            light: {
                primary: {
                    color:         '{primary.600}',
                    contrastColor: '#ffffff',
                    hoverColor:    '{primary.700}',
                    activeColor:   '{primary.800}',
                },
                highlight: {
                    background:      '{primary.50}',
                    focusBackground: '{primary.100}',
                    color:           '{primary.700}',
                    focusColor:      '{primary.800}',
                },
       
            },
            dark: {
                primary: {
                    color:         '{primary.400}',
                    contrastColor: '{primary.950}',
                    hoverColor:    '{primary.300}',
                    activeColor:   '{primary.200}',
                },
                highlight: {
                    background:      'rgba(139, 92, 246, 0.16)',
                    focusBackground: 'rgba(139, 92, 246, 0.24)',
                    color:           '{primary.300}',
                    focusColor:      '{primary.200}',
                },

            },
        },
    },

    components: {
        button: {
            root: {
                borderRadius: '0.625rem',
                paddingX:     '1rem',
                paddingY:     '0.5rem',
                sm: {
                    fontSize: '0.75rem',
                    paddingX: '0.75rem',
                    paddingY: '0.375rem',
                },
                lg: {
                    fontSize: '1rem',
                    paddingX: '1.25rem',
                    paddingY: '0.625rem',
                },
            },
        },
        inputtext: {
            root: {
                borderRadius: '0.625rem',
                paddingX:     '0.75rem',
                paddingY:     '0.5rem',
                sm: {
                    fontSize: '0.75rem',
                    paddingX: '0.625rem',
                    paddingY: '0.375rem',
                },
            },
        },
        select: {
            root: {
                borderRadius: '0.625rem',
                paddingX:     '0.75rem',
                paddingY:     '0.5rem',
            },
        },
        multiselect: {
            root: {
                borderRadius: '0.625rem',
                paddingX:     '0.75rem',
                paddingY:     '0.5rem',
            },
        },
        textarea: {
            root: {
                borderRadius: '0.625rem',
                paddingX:     '0.75rem',
                paddingY:     '0.625rem',
            },
        },
        inputnumber: {
            button: {
                borderRadius: '0.625rem',
            },
        },
        datatable: {
            header: {
                padding: '0.875rem 1rem',
            },
            headerCell: {
                padding: '0.875rem 1rem',
            },
            bodyCell: {
                padding: '0.875rem 1rem',
            },
        },
        card: {
            root: {
                borderRadius: '1rem',
            },
        },
        dialog: {
            root: {
                borderRadius: '1rem',
            },
        },
        toast: {
            root: {
                borderRadius: '0.875rem',
            },
        },
        popover: {
            root: {
                borderRadius: '0.875rem',
            },
        },
        menu: {
            root: {
                borderRadius: '0.875rem',
            },
        },
        tag: {
            root: {
                borderRadius: '0.5rem',
            },
        },
        badge: {
            root: {
                borderRadius: '0.5rem',
            },
        },
        chip: {
            root: {
                borderRadius: '0.5rem',
            },
        },
        paginator: {
            root: {
                borderRadius: '0.625rem',
            },
        },
    },
})

export default CosmosPreset
