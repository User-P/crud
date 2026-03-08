import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'

/**
 * CosmosPreset — PrimeVue theme alineado con la paleta DSI.
 * Verde #5bb56a, azul #0b4261, gris #64666a.
 */
const CosmosPreset = definePreset(Aura, {
    primitive: {
        primary: {
            50:  '#e8f0f4',
            100: '#c5dae6',
            200: '#9ec0d6',
            300: '#6fa3c2',
            400: '#3d85ad',
            500: '#156b8f',
            600: '#0b4261',
            700: '#093552',
            800: '#072944',
            900: '#051e35',
            950: '#031220',
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
            50:  '{primary.50}',
            100: '{primary.100}',
            200: '{primary.200}',
            300: '{primary.300}',
            400: '{primary.400}',
            500: '{primary.500}',
            600: '{primary.600}',
            700: '{primary.700}',
            800: '{primary.800}',
            900: '{primary.900}',
            950: '{primary.950}',
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
                    color:         '#5bb56a',
                    contrastColor: '#0b4261',
                    hoverColor:    '#6bc67a',
                    activeColor:   '#4a9d58',
                },
                highlight: {
                    background:      'rgba(91, 181, 106, 0.16)',
                    focusBackground: 'rgba(91, 181, 106, 0.24)',
                    color:           '#6bc67a',
                    focusColor:      '#5bb56a',
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
