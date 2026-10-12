path = 'D:/projects/klinik-git/src/modules/Operational/Inventory/presentations/views/InventoryIndex.vue'
with open(path, encoding='utf-8') as f:
    lines = f.readlines()
for i, l in enumerate(lines[:135], 1):
    print(f'{i:3}: {l}', end='')
