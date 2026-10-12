import os

models_dir = r'd:\projects\klinik-starter\src\app\Models'

def fix_file(filename):
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'r') as f:
        content = f.read()
    
    if 'use StandardizesModel' not in content:
        return False
        
    if 'public $incrementing = false;' in content:
        return False

    lines = content.splitlines()
    new_lines = []
    class_found = False
    for line in lines:
        new_lines.append(line)
        if 'class ' in line and '{' in line and not class_found:
            class_found = True
            new_lines.append('')
            new_lines.append('    protected $primaryKey = \'uuid\';')
            new_lines.append('    public $incrementing = false;')
            new_lines.append('    protected $keyType = \'string\';')
        elif 'class ' in line and not '{' in line and not class_found:
            # Handle class declaration spread across multiple lines
            pass
        elif '{' in line and not class_found and 'class ' in content:
             # Found the opening bracket after class declaration
             class_found = True
             new_lines.append('    protected $primaryKey = \'uuid\';')
             new_lines.append('    public $incrementing = false;')
             new_lines.append('    protected $keyType = \'string\';')

    # Cleanup duplicate primaryKey if it was already there
    final_content = '\n'.join(new_lines)
    # If it already had protected $primaryKey = 'uuid';, my simple insertion might have duplicated it.
    # Let's do a smarter replacement.
    
    if 'protected $primaryKey = \'uuid\';' in content:
        # Just add the other two
        if 'public $incrementing = false;' not in content:
            final_content = content.replace('protected $primaryKey = \'uuid\';', 
                                            "protected $primaryKey = 'uuid';\n    public $incrementing = false;\n    protected $keyType = 'string';")
    else:
        # Need to find where to insert
        # Let's just use the previous logic but more carefully
        pass
        
    with open(filepath, 'w') as f:
        f.write(final_content)
    return True

for filename in os.listdir(models_dir):
    if filename.endswith('.php'):
        if fix_file(filename):
            print(f"Fixed {filename}")
