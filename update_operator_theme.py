import os
import glob
import re

files = glob.glob('resources/views/livewire/operator/**/*.blade.php', recursive=True)

for filepath in files:
    with open(filepath, 'r') as f:
        content = f.read()

    # The main wrapper in logbook.blade.php
    content = content.replace('bg-slate-50 min-h-screen', 'bg-transparent min-h-screen')
    
    # Generic replacements
    content = re.sub(r'\bbg-white\b', 'mkt-surface', content)
    content = re.sub(r'\bbg-slate-50\b', 'mkt-surface', content) # or mkt-bg, but usually surface inside
    content = re.sub(r'\btext-slate-800\b', 'mkt-text', content)
    content = re.sub(r'\btext-slate-700\b', 'mkt-text', content)
    content = re.sub(r'\btext-slate-400\b', 'mkt-text-muted', content)
    content = re.sub(r'\btext-slate-300\b', 'mkt-text-muted', content)
    content = re.sub(r'\bborder-slate-100\b', 'mkt-border', content)
    content = re.sub(r'\bborder-slate-200\b', 'mkt-border', content)
    content = re.sub(r'\bborder-slate-50\b', 'mkt-border', content)
    content = re.sub(r'\bbg-slate-100\b', 'mkt-input', content)
    
    with open(filepath, 'w') as f:
        f.write(content)

print("Updated operator files with mkt- theme classes.")
