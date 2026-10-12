import os

models_dir = r'd:\projects\klinik-starter\src\app\Models'
import_line = 'use Illuminate\Database\Eloquent\Factories\HasFactory;\n'
use_line = '    use HasFactory'

for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        filepath = os.path.join(models_dir, filename)
        with open(filepath, 'r') as f:
            content = f.read()
        
        if use_line in content and import_line not in content:
            print(f"Fixing {filename}...")
            # Insert import after namespace
            if 'namespace App\\Models;' in content:
                new_content = content.replace('namespace App\\Models;', 'namespace App\\Models;\n\n' + import_line)
                # Cleanup double newlines if any
                new_content = new_content.replace('\n\n\n' + import_line, '\n\n' + import_line)
                with open(filepath, 'w') as f:
                    f.write(new_content)
            else:
                print(f"Could not find namespace in {filename}")
