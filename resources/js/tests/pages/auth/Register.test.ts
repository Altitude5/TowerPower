import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import Register from '@/Pages/auth/Register.vue';
import axios from 'axios';

vi.mock('axios');
vi.stubGlobal('route', (name: string) => name);

// Stub the Head component
vi.mock('@inertiajs/vue3', async (importOriginal) => {
    const mod = await importOriginal<typeof import('@inertiajs/vue3')>();
    return {
        ...mod,
        Head: {
            render: () => null
        }
    };
});

describe('Register.vue', () => {
    it('renders the registration form', () => {
        const wrapper = mount(Register, {
            props: { cities: [{ id: 1, name: 'Test City' }] },
            global: {
                stubs: ['AuthBase', 'Head', 'Form', 'SearchableSelect', 'Input', 'Label', 'Button', 'Dialog', 'InputError', 'PasswordInput', 'TextLink', 'Spinner']
            }
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('shows tower confirmation when a single tower is found', async () => {
        const wrapper = mount(Register, {
            props: { cities: [{ id: 1, name: 'Test City' }] },
            global: {
                stubs: {
                    AuthBase: false,
                    Head: false,
                    Form: false,
                    SearchableSelect: true,
                    Dialog: true,
                    Spinner: true,
                    InputError: true,
                    PasswordInput: true,
                    TextLink: true
                }
            }
        });

        axios.get.mockImplementation((url: string) => {
            if (url === 'geo.streets') return Promise.resolve({ data: [{ id: 1, name: 'Test Street' }] });
            if (url === 'geo.towers') return Promise.resolve({ data: { found: true, tower: { id: 10, name: 'Test Tower', full_address: 'Test Address 42' } } });
            return Promise.resolve({ data: [] });
        });

        // Set state
        wrapper.vm.selectedCityId = 1;
        await wrapper.vm.$nextTick();
        wrapper.vm.selectedStreetId = 1;
        await wrapper.vm.$nextTick();
        
        const input = wrapper.find('input#house_number');
        await input.setValue('42');
        await wrapper.vm.$nextTick();

        // Trigger the debounced search manually
        await wrapper.vm.fetchTower();
        await wrapper.vm.$nextTick();
        
        // Check UI for confirmation state elements
        expect(wrapper.find('h3').exists()).toBe(true);
        expect(wrapper.find('h3').text()).toContain('Test Tower');
        expect(wrapper.find('button.w-full').exists()).toBe(true);
        expect(wrapper.find('button.w-full').text()).toContain('Yes, this is my Tower!');
    });
});
