import os
import re

models_dir = r'd:\projects\klinik-starter\src\app\Models'

def fix_file(filename):
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if 'use StandardizesModel' not in content:
        return False
        
    if 'public $incrementing = false;' in content:
        return False

    # Standardize whitespace and quotes
    # Replace protected $primaryKey = 'uuid'; or "uuid"; with explicit block
    # Note: I'll use a very broad match for primaryKey and replace it.
    pattern = r'protected\s+\$primaryKey\s*=\s*[\'"]uuid[\'"]\s*;'
    replacement = "protected $primaryKey = 'uuid';\n    public $incrementing = false;\n    protected $keyType = 'string';"
    
    if re.search(pattern, content):
        new_content = re.sub(pattern, replacement, content)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    
    # If not found, insert after traits
    lines = content.splitlines()
    new_lines = []
    inserted = False
    for line in lines:
        new_lines.append(line)
        if ('trait ' not in line) and ('use ' in line) and ('StandardizesModel' in line) and not inserted:
             # Find the end of the use line
             if ';' in line:
                 new_lines.append("    protected $primaryKey = 'uuid';")
                 new_lines.append("    public $incrementing = false;")
                 new_lines.append("    protected $keyType = 'string';")
                 inserted = True
                 
    if inserted:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write('\n'.join(new_lines) + '\n')
        return True

    return False

for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        try:
            if fix_file(filename):
                print(f"Fixed {filename}")
        except Exception as e:
            print(f"Error fixing {filename}: {e}")
