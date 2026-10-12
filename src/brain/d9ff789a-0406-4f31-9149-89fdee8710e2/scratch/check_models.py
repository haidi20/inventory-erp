import os

models_dir = r'd:\projects\klinik-starter\src\app\Models'
for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        filepath = os.path.join(models_dir, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        print(f"{filename}: StandardizesModel={'StandardizesModel' in content}, incrementing={'public $incrementing = false;' in content}")
