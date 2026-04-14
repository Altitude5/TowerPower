import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import QuantityButton from './QuantityButton.vue';
import { useForm } from '@inertiajs/vue3';

// Mock Inertia useForm
vi.mock('@inertiajs/vue3', () => ({
    useForm: vi.fn(),
}));

// Mock Ziggy route
vi.mock('ziggy-js', () => ({
    route: vi.fn(() => '/mock-route'),
}));

describe('QuantityButton.vue', () => {
    let mockForm: any;

    beforeEach(() => {
        vi.clearAllMocks();
        
        mockForm = {
            post: vi.fn(),
            transform: vi.fn().mockReturnThis(),
            processing: false,
        };
        
        (useForm as any).mockReturnValue(mockForm);
    });

    it('renders the formatted quantity for Unit type', () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 5,
                priceType: 'Unit',
            },
        });

        expect(wrapper.text()).toContain('5');
        expect(wrapper.text()).not.toContain('kg');
        expect(wrapper.text()).not.toContain('L');
    });

    it('renders the formatted quantity and unit for Weight type', () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 1.5,
                priceType: 'Weight',
            },
        });

        expect(wrapper.text()).toContain('1.50');
        expect(wrapper.text()).toContain('kg');
    });

    it('renders the formatted quantity and unit for Volume type', () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 2,
                priceType: 'Volume',
            },
        });

        expect(wrapper.text()).toContain('2.00');
        expect(wrapper.text()).toContain('L');
    });

    it('increments Unit type by 1', async () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 5,
                priceType: 'Unit',
            },
        });

        const incrementBtn = wrapper.findAll('button')[1];
        await incrementBtn.trigger('click');

        // Check transform callback
        const transformCallback = mockForm.transform.mock.calls[0][0];
        const transformedData = transformCallback();
        
        expect(transformedData).toEqual({
            absolute: true,
            quantity: 6,
        });
        
        expect(mockForm.post).toHaveBeenCalled();
    });

    it('decrements Weight type by 0.25', async () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 1.5,
                priceType: 'Weight',
            },
        });

        const decrementBtn = wrapper.findAll('button')[0];
        await decrementBtn.trigger('click');

        const transformCallback = mockForm.transform.mock.calls[0][0];
        const transformedData = transformCallback();
        
        expect(transformedData).toEqual({
            absolute: true,
            weight: 1.25,
        });
    });

    it('increments Volume type by 0.5', async () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 1,
                priceType: 'Volume',
            },
        });

        const incrementBtn = wrapper.findAll('button')[1];
        await incrementBtn.trigger('click');

        const transformCallback = mockForm.transform.mock.calls[0][0];
        const transformedData = transformCallback();
        
        expect(transformedData).toEqual({
            absolute: true,
            volume: 1.5,
        });
    });

    it('disables buttons when form is processing', () => {
        mockForm.processing = true;
        
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 5,
                priceType: 'Unit',
            },
        });

        const buttons = wrapper.findAll('button');
        expect(buttons[0].attributes('disabled')).toBeDefined();
        expect(buttons[1].attributes('disabled')).toBeDefined();
    });

    it('allows negative quantity values based on current implementation', async () => {
        const wrapper = mount(QuantityButton, {
            props: {
                productId: 1,
                quantity: 0,
                priceType: 'Unit',
            },
        });

        const decrementBtn = wrapper.findAll('button')[0];
        await decrementBtn.trigger('click');

        const transformCallback = mockForm.transform.mock.calls[0][0];
        const transformedData = transformCallback();
        
        expect(transformedData).toEqual({
            absolute: true,
            quantity: -1,
        });
    });
});
