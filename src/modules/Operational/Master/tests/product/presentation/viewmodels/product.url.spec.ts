import { describe, expect, it } from 'vitest';
import { productUrlFilters } from '@modules/Operational/Master/presentations/viewmodels/product.vm';

describe('product URL filters', () => {
    it('keeps shareable filters and removes empty values', () => {
        const query = productUrlFilters({
            search: 'paracetamol',
            filter_code: 'PAR',
            filter_unit: '',
            page: 2,
            per_page: 25,
        });

        expect(query).toEqual({
            search: 'paracetamol',
            filter_code: 'PAR',
            page: '2',
            per_page: '25',
        });
    });
});
