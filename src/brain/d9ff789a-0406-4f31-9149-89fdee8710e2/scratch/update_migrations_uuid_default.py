import os
import re

directory = r'd:\projects\klinik-starter\src\database\migrations\v1'
# Match $table->char/uuid('uuid', ...) with ->primary()
# We want to catch the line that defines the uuid primary key
pattern = re.compile(r"(\$table->(?:char|uuid)\(['\"]uuid['\"].*?->primary\(\))(?!.*?->default)")

for filename in os.listdir(directory):
    if filename.endswith('.php'):
        path = os.path.join(directory, filename)
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Look for lines that define a uuid primary key BUT don't have a default yet
        # We'll do a line-by-line replacement to be safe
        lines = content.splitlines()
        new_lines = []
        changed = False
        
        for line in lines:
            if '->primary()' in line and ('char(\'uuid\'' in line or 'char("uuid"' in line or 'uuid(\'uuid\'' in line or 'uuid("uuid"' in line):
                if '->default(' not in line:
                    # Match the portion up to ->primary()
                    match = re.search(r"(\$table->(?:char|uuid)\(['\"]uuid['\"].*?->primary\(\))", line)
                    if match:
                        line = line.replace(match.group(1), match.group(1) + "->default(\DB::raw('(UUID())'))")
                        changed = True
            new_lines.append(line)
        
        if changed:
            with open(path, 'w', encoding='utf-8') as f:
                f.write('\n'.join(new_lines) + '\n')
            print(f"Updated {filename}")
