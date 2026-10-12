import os

directory = r'd:\projects\klinik-starter\src\app\Models'
import_line = "use App\Traits\HasBranchScope;\n"

for filename in os.listdir(directory):
    if filename.endswith('.php'):
        path = os.path.join(directory, filename)
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if 'use HasBranchScope' in content and 'use App\Traits\HasBranchScope;' not in content:
            # Add the import after the namespace or after other traits
            lines = content.splitlines()
            new_lines = []
            added = False
            for line in lines:
                new_lines.append(line)
                if line.startswith('namespace App\Models;') and not added:
                    new_lines.append("")
                    new_lines.append(import_line.strip())
                    added = True
            
            if added:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write('\n'.join(new_lines) + '\n')
                print(f"Fixed imports in {filename}")
