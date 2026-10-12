import os
import re

models_dir = r'd:\projects\klinik-starter\src\app\Models'

def fix_file(filename):
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'r') as f:
        content = f.read()
    
    if 'use StandardizesModel' not in content:
        return False
        
    if 'public $incrementing = false;' in content:
        return False

    # Standardize whitespace and quotes
    # Replace protected $primaryKey = 'uuid'; or "uuid"; with explicit block
    pattern = r'protected\s+\$primaryKey\s*=\s*[\'"]uuid[\'"]\s*;'
    replacement = "protected $primaryKey = 'uuid';\n    public $incrementing = false;\n    protected $keyType = 'string';"
    
    if re.search(pattern, content):
        new_content = re.sub(pattern, replacement, content)
        with open(filepath, 'w') as f:
            f.write(new_content)
        return True
    else:
        # If primaryKey not found, insert after traits
        lines = content.splitlines()
        new_lines = []
        in_class = False
        inserted = False
        for line in lines:
            new_lines.append(line)
            if 'class ' in line and not in_class:
                in_class = True
            if in_class and 'use ' in line and not inserted:
                # Wait until the end of trait use block?
                pass
            if in_class and '{' in line and not inserted:
                 # Poor man's insertion: after opening bracket
                 new_lines.append('')
                 new_lines.append("    protected $primaryKey = 'uuid';")
                 new_lines.append("    public $incrementing = false;")
                 new_lines.append("    protected $keyType = 'string';")
                 inserted = True
        
        if inserted:
            with open(filepath, 'w') as f:
                f.write('\n'.join(new_lines) + '\n')
            return True

    return False

for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        if fix_file(filename):
            print(f"Fixed {filename}")
