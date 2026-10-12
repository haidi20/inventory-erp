path = 'D:/projects/klinik-git/src/modules/Operational/Inventory/presentations/views/InventoryIndex.vue'
with open(path, encoding='utf-8') as f:
    c = f.read()
import re
for i, line in enumerate(c.splitlines(), 1):
    if 'expandedKey' in line:
        print(f'{i}: {line}')
