import { z } from 'zod';

const optionalText = (label: string, max: number) =>
    z.string().max(max, `${label} maksimal ${max} karakter.`).optional().or(z.literal(''));

const optionalNumber = (label: string) =>
    z.preprocess(
        (value) => (value === '' || value === null || value === undefined ? undefined : Number(value)),
        z
            .number({ message: `${label} harus berupa angka.` })
            .min(0, `${label} tidak boleh negatif.`)
            .optional(),
    );

export const productFormSchema = z.object({
    code: z
        .string({ message: 'Kode produk wajib diisi.' })
        .trim()
        .min(1, 'Kode produk wajib diisi.')
        .max(50, 'Kode produk maksimal 50 karakter.'),
    name: z
        .string({ message: 'Nama produk wajib diisi.' })
        .trim()
        .min(1, 'Nama produk wajib diisi.')
        .max(255, 'Nama produk maksimal 255 karakter.'),
    barcode: optionalText('Barcode', 100),
    description: optionalText('Deskripsi', 500),
    remarks: optionalText('Keterangan', 500),
    item_name_alt: optionalText('Kemasan', 255),
    item_type: z.string().optional(),
    group_uuid: z.string().optional(),
    type_uuid: z.string().optional(),
    brand_uuid: z.string().optional(),
    origin_uuid: z.string().optional(),
    grade_uuid: z.string().optional(),
    kind: optionalText('Jenis', 100),
    location: optionalText('Lokasi', 100),
    base_unit_uuid: z.string().optional(),
    price_buy: optionalNumber('Harga beli'),
    price_sell: optionalNumber('Harga jual'),
    opening_cost: optionalNumber('HPP awal'),
    min_stock: optionalNumber('Minimum stok'),
    max_stock: optionalNumber('Maks stok'),
    max_order: optionalNumber('Maks order'),
    is_active: z.boolean().optional(),
    is_assembly: z.boolean().optional(),
    coa_sales_uuid: z.string().optional(),
    coa_inventory_uuid: z.string().optional(),
    coa_sales_discount_uuid: z.string().optional(),
    coa_hpp_uuid: z.string().optional(),
    coa_sales_return_uuid: z.string().optional(),
    coa_purchase_return_uuid: z.string().optional(),
    coa_suspend_uuid: z.string().optional(),
    units: z.array(z.record(z.string(), z.unknown())).optional(),
    accounts: z.array(z.record(z.string(), z.unknown())).optional(),
    opening_balances: z.array(z.record(z.string(), z.unknown())).optional(),
    prices: z.array(z.record(z.string(), z.unknown())).optional(),
    discounts: z.array(z.record(z.string(), z.unknown())).optional(),
    image: z.unknown().optional(),
    images: z.array(z.unknown()).optional(),
    existing_image_paths: z.array(z.string()).optional(),
});

export function validateProductForm(data: Record<string, unknown>): Record<string, string> {
    const result = productFormSchema.safeParse(data);
    if (result.success) return {};
    const errors: Record<string, string> = {};
    for (const issue of result.error.issues) {
        const key = String(issue.path[0] ?? '');
        if (key && !errors[key]) errors[key] = issue.message;
    }
    return errors;
}