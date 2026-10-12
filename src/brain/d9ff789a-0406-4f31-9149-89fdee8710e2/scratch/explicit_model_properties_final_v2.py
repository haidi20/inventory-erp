import os
import re

models_dir = r'd:\projects\klinik-starter\src\app\Models'

def fix_file(filename):
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if 'StandardizesModel' not in content:
        return False
        
    if 'public $incrementing = false;' in content:
        return False

    print(f"Processing {filename}...")

    # Pattern for primaryKey line
    pattern = r'protected\s+\$primaryKey\s*=\s*[\'"]uuid[\'"]\s*;'
    
    match = re.search(pattern, content)
    if match:
        replacement = "protected $primaryKey = 'uuid';\n    public $incrementing = false;\n    protected $keyType = 'string';"
        new_content = content.replace(match.group(0), replacement)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    
    # If not found, insert after Opening bracket
    lines = content.splitlines()
    new_lines = []
    inserted = False
    for line in lines:
        new_lines.append(line)
        if re.search(r'class\s+\w+', line) and '{' in line and not inserted:
             in_class = True
             new_lines.append("    protected $primaryKey = 'uuid';")
             new_lines.append("    public $incrementing = false;")
             new_lines.append("    protected $keyType = 'string';")
             inserted = True
        elif re.search(r'class\s+\w+', line) and not inserted:
             in_class = True
        elif '{' in line and not inserted:
             # Very likely class start
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
                print(f"Fixed {filename} successfully")
        except Exception as e:
            print(f"Error fixing {filename}: {e}")
