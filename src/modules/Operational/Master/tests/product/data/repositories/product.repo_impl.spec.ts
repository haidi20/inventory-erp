import { describe, expect, it, vi } from 'vitest';
import apiService from '@modules/Core/shared/core/api_service';
import { ProductRepoImpl } from '@modules/Operational/Master/data/repositories/product.repo_impl';

describe('ProductRepoImpl tab payload', () => {
    it('serializes tab rows when uploading product files', async () => {
        const post = vi.spyOn(apiService, 'post').mockResolvedValue({ data: { uuid: 'product-1' } } as any);
        const repo = new ProductRepoImpl();

        const result = await repo.save({
            code: 'P-001',
            name: 'Produk TDD',
            is_active: true,
            image: new File(['image'], 'product.png', { type: 'image/png' }),
            images: [],
            existing_image_paths: [],
            units: [{ unit_uuid: 'unit-1', conversion_ratio: 1, is_default: true, is_active: true }],
            opening_balances: [{ location_uuid: 'location-1', opening_qty: 4, period: '2026-09' }],
            prices: [{ price_level_uuid: 'level-1', price_buy: 10, price_sell: 15, discount_amount: 0, discount_percent: 0, base_qty: 1, is_active: true }],
            discounts: [{ quantity: 5, discount_percent: 10, discount_amount: 0, base_qty: 1 }],
            accounts: [{ account_role: 'sales', account_uuid: 'account-1' }],
        });

        expect(result.isRight()).toBe(true);
        const payload = post.mock.calls[0]?.[1];
        expect(payload).toBeInstanceOf(FormData);
        expect(JSON.parse((payload as FormData).get('units') as string)).toEqual(expect.arrayContaining([expect.objectContaining({ unit_uuid: 'unit-1' })]));
        expect(JSON.parse((payload as FormData).get('opening_balances') as string)).toEqual(expect.arrayContaining([expect.objectContaining({ opening_qty: 4 })]));
        expect(JSON.parse((payload as FormData).get('prices') as string)).toEqual(expect.arrayContaining([expect.objectContaining({ price_sell: 15 })]));
        expect(JSON.parse((payload as FormData).get('discounts') as string)).toEqual(expect.arrayContaining([expect.objectContaining({ discount_percent: 10 })]));
        expect(JSON.parse((payload as FormData).get('accounts') as string)).toEqual(expect.arrayContaining([expect.objectContaining({ account_uuid: 'account-1' })]));
    });
});
