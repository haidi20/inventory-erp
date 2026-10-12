import os

models_dir = r'd:\projects\klinik-starter\src\app\Models'

def fix_file(filename):
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'r') as f:
        content = f.read()
    
    lines = content.splitlines()
    new_content = []
    namespace_found = False
    
    needed_imports = []
    
    # Logic for Model
    if 'extends Model' in content and 'use Illuminate\\Database\\Eloquent\\Model;' not in content:
        needed_imports.append('use Illuminate\\Database\\Eloquent\\Model;')
    
    # Logic for HasFactory
    if 'use HasFactory' in content and 'use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;' not in content:
        needed_imports.append('use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;')

    if not needed_imports:
        return False

    for line in lines:
        new_content.append(line)
        if line.startswith('namespace App\\Models;'):
            namespace_found = True
            new_content.append('')
            for imp in needed_imports:
                new_content.append(imp)
    
    if namespace_found:
        with open(filepath, 'w') as f:
            f.write('\n'.join(new_content) + '\n')
        return True
    return False

for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        if fix_file(filename):
            print(f"Fixed {filename}")
