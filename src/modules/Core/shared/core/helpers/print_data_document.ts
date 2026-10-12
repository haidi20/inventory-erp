export interface PrintDataField {
    label: string;
    value: string;
}

export interface PrintDataDocument {
    title: string;
    subtitle?: string;
    fields: PrintDataField[];
}

const escapeHtml = (value: string): string => value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

export const printDataDocument = (documentData: PrintDataDocument): void => {
    const printWindow = window.open('', '_blank');

    if (!printWindow) {
        return;
    }

    const rows = documentData.fields.map((field) => `
        <tr>
            <th>${escapeHtml(field.label)}</th>
            <td>${escapeHtml(field.value)}</td>
        </tr>
    `).join('');

    printWindow.document.write(`
        <!doctype html>
        <html lang="id">
            <head>
                <meta charset="utf-8" />
                <title>${escapeHtml(documentData.title)}</title>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        margin: 0;
                        background: #f8fafc;
                        color: #0f172a;
                        font-family: Arial, sans-serif;
                        padding: 32px;
                    }
                    .print-card {
                        max-width: 760px;
                        margin: 0 auto;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 18px;
                        padding: 28px;
                    }
                    .print-header {
                        border-bottom: 3px solid #0f8b6f;
                        margin-bottom: 24px;
                        padding-bottom: 16px;
                    }
                    h1 {
                        margin: 0;
                        color: #0f8b6f;
                        font-size: 24px;
                    }
                    .subtitle {
                        margin-top: 8px;
                        color: #64748b;
                        font-size: 13px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border-bottom: 1px solid #e2e8f0;
                        padding: 13px 10px;
                        text-align: left;
                        vertical-align: top;
                    }
                    th {
                        width: 210px;
                        color: #475569;
                        font-size: 12px;
                        text-transform: uppercase;
                    }
                    td {
                        color: #0f172a;
                        font-size: 14px;
                        font-weight: 700;
                    }
                    .print-footer {
                        margin-top: 28px;
                        color: #64748b;
                        font-size: 11px;
                        text-align: right;
                    }
                    @media print {
                        body { background: #ffffff; padding: 0; }
                        .print-card { border: 0; border-radius: 0; max-width: none; }
                    }
                </style>
            </head>
            <body>
                <main class="print-card">
                    <header class="print-header">
                        <h1>${escapeHtml(documentData.title)}</h1>
                        ${documentData.subtitle ? `<div class="subtitle">${escapeHtml(documentData.subtitle)}</div>` : ''}
                    </header>
                    <table>
                        <tbody>${rows}</tbody>
                    </table>
                    <div class="print-footer">Dicetak dari Klinik 2026</div>
                </main>
                <script>
                    window.addEventListener('load', function () {
                        window.print();
                    });
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
};
